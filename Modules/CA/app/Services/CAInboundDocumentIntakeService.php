<?php

namespace Modules\CA\Services;

use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CADocument;
use Modules\CA\Models\CANotification;
use Modules\CA\Services\AI\Managers\AIManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

class CAInboundDocumentIntakeService
{
    public function __construct(
        private AIManager $aiManager,
        private CADocumentMatchingService $matchingService,
        private WhatsAppOutboundMessageService $outboundMessageService
    ) {}

    /**
     * Coordinate inbound WhatsApp document matching and processing.
     */
    public function processIntake(ConversationMessage $message, CAClient $client): void
    {
        $conversation = $message->conversation;
        if (!$conversation) {
            return;
        }

        // 1. Fetch pending/rejected requirements for this client
        $pendingRequirements = CAClientComplianceRequirement::whereHas('clientCompliance', function($q) use ($client) {
            $q->where('ca_client_id', $client->id);
        })
        ->whereIn('status', ['pending', 'rejected'])
        ->with(['clientCompliance.compliance'])
        ->get();

        // 2. Filter: If no pending required documents, exit silently or reply politely if configured
        if ($pendingRequirements->isEmpty()) {
            Log::info("CAInboundDocumentIntake: No pending requirements for client ID {$client->id}. Exiting intake.");
            
            // Optionally send polite reply
            $this->sendSessionReply($conversation, "We received your attachment, but there are currently no pending document requests on your profile. Thank you!");
            return;
        }

        // 3. File Type & Extension Check
        $localPath = $message->media_meta['local_path'] ?? null;
        $mimeType = $message->media_meta['mime_type'] ?? '';
        $filename = $message->media_meta['filename'] ?? 'document.pdf';
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExtensions) || !Storage::disk('public')->exists($localPath)) {
            Log::info("CAInboundDocumentIntake: Unsupported file extension or file missing on disk: {$ext}");
            $this->sendSessionReply($conversation, "Sorry, we could not process this file type. Please submit your document as a PDF or an Image (JPG, PNG).");
            return;
        }

        // 4. AI Document Classification
        try {
            $classification = $this->classifyDocumentViaAI($localPath, $mimeType);
        } catch (Exception $e) {
            Log::error("CAInboundDocumentIntake AI Classification failed: " . $e->getMessage());
            // Fallback mock classification so the flow works even if AI fails/quota exceeded
            $classification = [
                'detected_document_type' => 'unknown',
                'detected_document_name' => 'Unknown Document',
                'confidence' => 0.4,
                'reason' => 'AI execution exception.',
            ];
        }

        // 5. Run the Matching Engine
        $matchResult = $this->matchingService->match($classification, $pendingRequirements);
        $status = $matchResult['status'];
        $matchedReq = $matchResult['matched_requirement'];

        // 6. Security Copy: Copy file from public storage to secure private disk
        $companyId = $conversation->company_id;
        $secureFilename = time() . '_' . uniqid() . '.' . $ext;
        $secureDirectory = "ca_documents/{$companyId}/{$client->id}";
        $securePath = "{$secureDirectory}/{$secureFilename}";

        // Read public file binary and put to private disk
        $binary = Storage::disk('public')->get($localPath);
        Storage::disk('local')->put($securePath, $binary);
        
        $fileSize = Storage::disk('public')->size($localPath);

        // 7. Create local CADocument record
        $document = CADocument::create([
            'company_id' => $companyId,
            'ca_client_id' => $client->id,
            'ca_client_compliance_id' => $matchedReq ? $matchedReq->ca_client_compliance_id : null,
            'ca_client_compliance_requirement_id' => $matchedReq ? $matchedReq->id : null,
            'document_name' => $matchedReq ? $matchedReq->name : $classification['detected_document_name'],
            'document_type' => $classification['detected_document_type'],
            'mime_type' => $mimeType,
            'extension' => $ext,
            'storage_disk' => 'local',
            'storage_path' => $securePath,
            'original_filename' => $filename,
            'file_size' => $fileSize,
            'status' => 'uploaded',
            'metadata_json' => [
                'ai_classification' => $classification,
                'matching_outcome'  => $status,
                'source'            => 'whatsapp',
                'whatsapp_msg_id'   => $message->external_message_id,
            ],
        ]);

        // 8. Update requirement status to under_review if matched
        if ($status === 'matched' && $matchedReq) {
            $matchedReq->update(['status' => 'under_review']);
        }

        // 9. Dispatch CA Notification (pending review)
        CANotification::create([
            'company_id' => $companyId,
            'ca_client_id' => $client->id,
            'contact_id' => $conversation->contact_id,
            'type' => $status === 'matched' ? 'document_matched' : 'match_failed',
            'title' => $status === 'matched' ? 'Inbound Document Auto-Matched' : 'Inbound Document Match Failed',
            'message' => $status === 'matched' 
                ? "Document {$filename} from client {$client->client_name} was auto-matched to: {$matchedReq->name}."
                : "A document {$filename} was received from {$client->client_name} but requires manual alignment.",
            'status' => 'pending',
            'metadata_json' => [
                'ca_document_id' => $document->id,
                'ai_classification' => $classification,
                'matching_outcome' => $status,
            ],
        ]);

        // 10. Reply to Client on WhatsApp (Session Message)
        $replyText = $this->buildWhatsAppResponse($status, $matchedReq, $pendingRequirements, $classification);
        $this->sendSessionReply($conversation, $replyText);
    }

    /**
     * Call AI provider to classify the document.
     */
    private function classifyDocumentViaAI(string $localPath, string $mimeType): array
    {
        $provider = $this->aiManager->provider();

        $systemPrompt = <<<EOT
You are an expert Indian Chartered Accountancy document classifier.
Analyze the document type (e.g. GST Certificate, PAN, Aadhaar, Bank Statement, ITR, Form 16, Purchase/Sales Register).
Return structured JSON only with keys: detected_document_type (slug format e.g. bank_statement, pan_card), detected_document_name (clean name), confidence (0.0 to 1.0), reason (short string).
EOT;

        $userPrompt = "Analyze document format mime-type {$mimeType} path {$localPath}.";

        // Structured JSON parser call
        return $provider->generateStructuredResponse($systemPrompt, $userPrompt, [], $localPath);
    }

    /**
     * Construct the WhatsApp response text for the client.
     */
    private function buildWhatsAppResponse(string $status, ?CAClientComplianceRequirement $matchedReq, $pendingRequirements, array $classification): string
    {
        if ($status === 'matched' && $matchedReq) {
            $reply = "✓ Document Received: *{$classification['detected_document_name']}*\n";
            $reply .= "We've matched this to your requirement: *{$matchedReq->name}* (Status: Under Review).\n\n";

            // List remaining pending requirements
            $remaining = $pendingRequirements->filter(fn($r) => $r->id !== $matchedReq->id);
            if ($remaining->isNotEmpty()) {
                $reply .= "⏳ *Remaining Pending Documents:*\n";
                foreach ($remaining as $rem) {
                    $due = $rem->next_due_date ? Carbon::parse($rem->next_due_date)->format('d M Y') : 'N/A';
                    $reply .= "- {$rem->name} (Due: {$due})\n";
                }
            } else {
                $reply .= "🎉 Awesome! All your pending documents have been submitted.";
            }

            return $reply;
        }

        // Unmatched/Low Confidence
        $reply = "We received your attachment, but our automated system could not confidently classify or match it to a pending requirement.\n\n";
        $reply .= "⏳ *Please submit one of your pending documents:*\n";
        foreach ($pendingRequirements as $rem) {
            $due = $rem->next_due_date ? Carbon::parse($rem->next_due_date)->format('d M Y') : 'N/A';
            $reply .= "- {$rem->name} (Due: {$due})\n";
        }

        return $reply;
    }

    /**
     * Send direct session WhatsApp message to conversation.
     */
    private function sendSessionReply($conversation, string $text): void
    {
        $msg = ConversationMessage::create([
            'conversation_id'     => $conversation->id,
            'direction'           => 'outbound',
            'message_type'        => 'text',
            'body'                => $text,
            'status'              => 'queued',
        ]);

        $this->outboundMessageService->sendConversationMessage($msg);
    }
}
