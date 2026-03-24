<?php

namespace App\Services\WhatsApp;

use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Support\Facades\Log;

class WhatsAppOutboundMessageService
{
    public function __construct(
        protected WhatsAppGraphClient $graphClient
    ) {}

    /**
     * Coordinate sending a conversation message via WhatsApp Cloud API.
     */
    public function sendConversationMessage(ConversationMessage $message): bool
    {
        try {
            $conversation = $message->conversation;
            if (!$conversation) {
                Log::error("WhatsApp Outbound: Message has no conversation", ['message_id' => $message->id]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $localNumber = $conversation->whatsappPhoneNumber;
            if (!$localNumber) {
                Log::error("WhatsApp Outbound: Conversation has no associated WhatsApp number", ['conversation_id' => $conversation->id]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $account = $localNumber->account;
            if (!$account || !$account->access_token) {
                Log::error("WhatsApp Outbound: WhatsApp account or access token missing", ['number_id' => $localNumber->id]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $to = $conversation->contact_phone;
            $phoneNumberId = $localNumber->phone_number_id;
            $accessToken = $account->access_token;

            Log::info("WhatsApp Outbound: Attempting to send message", [
                'message_id' => $message->id,
                'phone_id' => $phoneNumberId,
                'to' => $to,
                'type' => $message->message_type
            ]);

            $result = $this->dispatchToMeta($message, $phoneNumberId, $accessToken, $to);

            if ($result['success']) {
                $message->update([
                    'external_message_id' => $result['message_id'],
                    'status' => 'sent',
                    'meta_payload' => $result['data']
                ]);
                return true;
            } else {
                $message->update([
                    'status' => 'failed',
                    'meta_payload' => ['error' => $result['error'] ?? 'Unknown error']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("WhatsApp Outbound Exception: " . $e->getMessage(), ['message_id' => $message->id]);
            $message->update(['status' => 'failed']);
            return false;
        }
    }

    /**
     * Dispatch the actual API call based on message type.
     */
    protected function dispatchToMeta(ConversationMessage $message, string $phoneNumberId, string $accessToken, string $to): array
    {
        if ($message->message_type === 'text') {
            return $this->graphClient->sendTextMessage($phoneNumberId, $accessToken, $to, $message->body);
        }

        if ($message->message_type === 'template') {
            // Template logic requires standard components structure or a builder
            // For now, assume body contains template name for basic check, 
            // but real template sends should use a specialized method.
            $templateName = $message->body; 
            return $this->graphClient->sendTemplate($phoneNumberId, $accessToken, $to, $templateName);
        }

        return [
            'success' => false,
            'error' => "Unsupported message type: {$message->message_type}"
        ];
    }
}
