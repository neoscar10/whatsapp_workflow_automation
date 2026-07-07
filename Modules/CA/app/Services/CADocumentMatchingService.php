<?php

namespace Modules\CA\Services;

use Illuminate\Support\Collection;
use Modules\CA\Models\CAClientComplianceRequirement;

class CADocumentMatchingService
{
    /**
     * Synonym expansion map: AI-detected slug terms → additional keywords to try.
     * This ensures common Indian CA document types map correctly to requirement names.
     */
    private array $synonymMap = [
        'gst_tax_invoice'          => ['gst', 'invoice', 'sales', 'tax'],
        'gst_sales_invoice'        => ['gst', 'invoice', 'sales'],
        'gst_purchase_invoice'     => ['gst', 'invoice', 'purchase'],
        'gst_return'               => ['gst', 'return', 'filing'],
        'gst_certificate'          => ['gst', 'certificate', 'registration'],
        'bank_statement'           => ['bank', 'statement', 'account'],
        'pan_card'                 => ['pan', 'card', 'identity'],
        'aadhaar'                  => ['aadhaar', 'aadhar', 'uid', 'identity'],
        'itr'                      => ['itr', 'income', 'tax', 'return'],
        'form_16'                  => ['form', '16', 'tds', 'salary'],
        'tds_certificate'          => ['tds', 'certificate', 'tax', 'deducted'],
        'balance_sheet'            => ['balance', 'sheet', 'accounts'],
        'profit_loss'              => ['profit', 'loss', 'p&l', 'pl'],
        'purchase_register'        => ['purchase', 'register', 'ledger'],
        'sales_register'           => ['sales', 'register', 'ledger'],
        'audit_report'             => ['audit', 'report'],
        'incorporation_certificate' => ['incorporation', 'certificate', 'company'],
    ];

    /**
     * Match AI classification outputs against client's pending compliance requirements.
     */
    public function match(array $aiResult, Collection $pendingRequirements): array
    {
        $detectedType = strtolower($aiResult['detected_document_type'] ?? '');
        $detectedName = strtolower($aiResult['detected_document_name'] ?? '');
        $confidence   = (float) ($aiResult['confidence'] ?? 0.0);

        if ($confidence < 0.55) {
            return [
                'status'              => 'low_confidence',
                'matched_requirement' => null,
                'candidates'          => collect(),
            ];
        }

        // Tokenize detected type slug (e.g. "gst_tax_invoice" → ["gst","tax","invoice"])
        $detectedTokens = $this->tokenize($detectedType);

        // Expand with synonyms if available
        $expandedTokens = array_unique(array_merge(
            $detectedTokens,
            $this->synonymMap[$detectedType] ?? []
        ));

        $candidates = collect();
        $scores     = [];

        // --- Pass 1: Token-overlap scoring against requirement names ---
        foreach ($pendingRequirements as $req) {
            $reqTokens      = $this->tokenize($req->name);
            $compTokens     = $this->tokenize($req->clientCompliance?->compliance?->name ?? '');
            $allReqTokens   = array_unique(array_merge($reqTokens, $compTokens));

            $overlap = count(array_intersect($expandedTokens, $allReqTokens));

            if ($overlap > 0) {
                $score = $overlap / max(count($expandedTokens), count($allReqTokens));
                $candidates->push($req);
                $scores[$req->id] = $score;
            }
        }

        // --- Pass 2: Exact substring fallback using detected_document_name ---
        if ($candidates->isEmpty() && $detectedName) {
            $detectedNameTokens = $this->tokenize($detectedName);
            foreach ($pendingRequirements as $req) {
                $reqTokens = $this->tokenize($req->name);
                $overlap   = count(array_intersect($detectedNameTokens, $reqTokens));
                if ($overlap > 0) {
                    $score = $overlap / max(count($detectedNameTokens), count($reqTokens));
                    $candidates->push($req);
                    $scores[$req->id] = $score;
                }
            }
        }

        if ($candidates->isEmpty()) {
            return [
                'status'              => 'not_matched',
                'matched_requirement' => null,
                'candidates'          => collect(),
            ];
        }

        // Sort candidates by overlap score (highest first)
        $candidates = $candidates->sortByDesc(fn($r) => $scores[$r->id] ?? 0)->values();

        // Auto-match: top candidate with high score AND confidence, only when unambiguous
        $topScore = $scores[$candidates->first()->id] ?? 0;
        if ($candidates->count() === 1 && $confidence >= 0.75 && $topScore >= 0.3) {
            return [
                'status'              => 'matched',
                'matched_requirement' => $candidates->first(),
                'candidates'          => $candidates,
            ];
        }

        // Multiple candidates or lower confidence → possible match (CA resolves manually)
        return [
            'status'              => 'possible_match',
            'matched_requirement' => null,
            'candidates'          => $candidates,
        ];
    }

    /**
     * Tokenize a string slug or name into lowercase keyword tokens.
     * Handles underscores, spaces, hyphens and common abbreviations.
     */
    private function tokenize(string $input): array
    {
        // Normalise separators to space
        $normalized = preg_replace('/[_\-\/]+/', ' ', $input);
        // Split on spaces, filter short filler words
        $stopWords = ['the', 'of', 'and', 'a', 'an', 'to', 'for', 'in', 'on', 'with', 'by'];
        $tokens = array_filter(
            explode(' ', strtolower(trim($normalized))),
            fn($t) => strlen($t) >= 2 && !in_array($t, $stopWords)
        );
        return array_values(array_unique($tokens));
    }
}

