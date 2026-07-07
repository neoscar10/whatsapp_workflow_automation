<?php

namespace Modules\CA\Listeners;

use App\Events\Chat\ChatMessageReceived;
use Modules\CA\Models\CAClient;
use Modules\CA\Services\CAInboundDocumentIntakeService;
use Illuminate\Support\Facades\Log;

class HandleInboundWhatsAppDocument
{
    /**
     * Handle the event.
     */
    public function handle(ChatMessageReceived $event): void
    {
        $message = $event->message;

        // 1. Filter out outbound messages
        if ($message->direction !== 'inbound') {
            return;
        }

        // 2. Filter out messages without attachment files
        if (!in_array($message->message_type, ['image', 'document', 'pdf', 'audio', 'video'])) {
            return;
        }

        $conversation = $message->conversation;
        if (!$conversation) {
            return;
        }

        // 3. Match inbound sender phone to a CAClient
        $fromPhone = preg_replace('/[^0-9]/', '', $conversation->contact_phone);
        
        // Find clients matching phone (might end with 10 digits, or direct matches)
        $client = CAClient::where('company_id', $conversation->company_id)
            ->where(function($q) use ($fromPhone) {
                $q->where('phone', 'like', '%' . substr($fromPhone, -10))
                  ->orWhere('phone', $fromPhone);
            })
            ->first();

        if (!$client) {
            Log::debug("HandleInboundWhatsAppDocument: Inbound attachment from non-CA client phone: {$fromPhone}");
            return;
        }

        // 4. Delegate to the CA document intake service
        try {
            $intakeService = app(CAInboundDocumentIntakeService::class);
            $intakeService->processIntake($message, $client);
        } catch (\Exception $e) {
            Log::error("HandleInboundWhatsAppDocument: Intake pipeline failed: " . $e->getMessage());
        }
    }
}
