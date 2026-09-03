<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Models\Contact\Contact;
use App\Models\User;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Support\Facades\DB;
use Exception;

class CampaignRecipientImportService
{
    /**
     * Import recipients from a CSV file.
     */
    public function importFromCsv(User $actor, Campaign $campaign, string $filePath): array
    {
        if (!$campaign->isDraft()) {
            throw new Exception("Recipients can only be imported into draft campaigns.");
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);
        
        if (!$header) {
            throw new Exception("Invalid CSV file.");
        }

        $phoneIndex = $this->findIndex($header, ['phone', 'whatsapp', 'number']);
        if ($phoneIndex === false) {
            throw new Exception("CSV must contain a 'phone' column.");
        }

        $nameIndex = $this->findIndex($header, ['name', 'full_name']);

        $summary = [
            'total' => 0,
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::transaction(function () use ($file, $header, $phoneIndex, $nameIndex, $campaign, $actor, &$summary) {
            while (($row = fgetcsv($file)) !== false) {
                $summary['total']++;
                
                try {
                    $phone = $row[$phoneIndex] ?? '';
                    if (empty($phone)) {
                        $summary['skipped']++;
                        continue;
                    }

                    $normalized = PhoneNumberNormalizer::normalize($phone);
                    if (!PhoneNumberNormalizer::isValid($normalized)) {
                        $summary['failed']++;
                        $summary['errors'][] = "Row {$summary['total']}: Invalid phone number format.";
                        continue;
                    }

                    $name = $nameIndex !== false ? ($row[$nameIndex] ?? '') : null;

                    // Check if contact exists
                    $contact = Contact::forCompany($actor->company_id)
                        ->where('normalized_phone', $normalized)
                        ->first();

                    // If not exists, we could create it, but as per rules, we keep it linked if available.
                    // For now, we'll just create the recipient.
                    
                    $personalization = [];
                    foreach ($header as $idx => $colName) {
                        if (in_array($idx, [$phoneIndex, $nameIndex])) continue;
                        $personalization[$colName] = $row[$idx] ?? null;
                    }

                    $isMessageable = $contact ? $contact->isMessageable() : true; // Assume true if manual import for now

                    CampaignRecipient::updateOrCreate(
                        ['campaign_id' => $campaign->id, 'normalized_phone' => $normalized],
                        [
                            'company_id' => $actor->company_id,
                            'contact_id' => $contact?->id,
                            'phone' => $phone,
                            'name' => $name ?? $contact?->name,
                            'source' => 'imported',
                            'status' => $isMessageable ? 'pending' : 'skipped',
                            'skip_reason' => $isMessageable ? null : 'Contact opted out',
                            'personalization_data' => $personalization,
                        ]
                    );

                    $summary['success']++;
                } catch (Exception $e) {
                    $summary['failed']++;
                    $summary['errors'][] = "Row {$summary['total']}: " . $e->getMessage();
                }
            }
        });

        fclose($file);

        // Update campaign recipient counts
        app(CampaignService::class)->recalculateStats($campaign);

        return $summary;
    }

    protected function findIndex(array $header, array $needles): int|bool
    {
        foreach ($header as $index => $column) {
            $column = (string) $column;
            // Clean BOM
            $column = preg_replace('/^[\x{EF}\x{BB}\x{BF}\x{FEFF}]/u', '', $column);
            $column = preg_replace('/^\xEF\xBB\xBF/', '', $column);
            $column = strtolower(trim($column));
            
            foreach ($needles as $needle) {
                if (str_contains($column, $needle)) {
                    return $index;
                }
            }
        }
        return false;
    }
}
