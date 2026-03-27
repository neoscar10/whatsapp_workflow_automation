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
        $correlationId = uniqid('wa_');
        
        try {
            $conversation = $message->conversation;
            if (!$conversation) {
                Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Message has no conversation", [
                    'message_id' => $message->id,
                    'file' => __FILE__,
                    'method' => __METHOD__
                ]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $localNumber = $conversation->whatsappPhoneNumber;
            if (!$localNumber) {
                Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Conversation has no associated WhatsApp number", [
                    'conversation_id' => $conversation->id,
                    'file' => __FILE__,
                    'method' => __METHOD__
                ]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $account = $localNumber->account;
            if (!$account || !$account->access_token) {
                Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: WhatsApp account or access token missing", [
                    'number_id' => $localNumber->id,
                    'file' => __FILE__,
                    'method' => __METHOD__
                ]);
                $message->update(['status' => 'failed']);
                return false;
            }

            $to = $conversation->contact_phone;
            $phoneNumberId = $localNumber->phone_number_id;
            $accessToken = $account->access_token;

            Log::info("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_START: Initiating send operation", [
                'message_id' => $message->id,
                'phone_id' => $phoneNumberId,
                'to' => $to,
                'type' => $message->message_type
            ]);

            $result = $this->dispatchToMeta($message, $phoneNumberId, $accessToken, $to, $correlationId);

            if ($result['success']) {
                Log::info("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_SUCCESS: Message acknowledged by provider", [
                    'message_id' => $message->id,
                    'external_id' => $result['message_id'] ?? null
                ]);

                $message->update([
                    'external_message_id' => $result['message_id'],
                    'status' => 'sent',
                    'meta_payload' => $result['data']
                ]);
                return true;
            } else {
                Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Service responded with error", [
                    'message_id' => $message->id,
                    'error' => $result['error'] ?? 'Unknown error',
                    'status' => $result['status'] ?? 'N/A'
                ]);

                $message->update([
                    'status' => 'failed',
                    'meta_payload' => ['error' => $result['error'] ?? 'Unknown error']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Exception caught during send operation", [
                'message_id' => $message->id,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => __METHOD__
            ]);
            $message->update(['status' => 'failed']);
            return false;
        }
    }

    /**
     * Dispatch the actual API call based on message type.
     */
    protected function dispatchToMeta(ConversationMessage $message, string $phoneNumberId, string $accessToken, string $to, string $correlationId): array
    {
        if ($message->message_type === 'text') {
            return $this->graphClient->sendTextMessage($phoneNumberId, $accessToken, $to, $message->body, $correlationId);
        }

        if ($message->message_type === 'template') {
            $payload = $message->meta_payload ?? [];
            $templateName = $payload['template_name'] ?? $message->body;
            $languageCode = $payload['language_code'] ?? 'en_US';
            $components = $payload['components'] ?? [];

            return $this->graphClient->sendTemplate($phoneNumberId, $accessToken, $to, $templateName, $languageCode, $components, $correlationId);
        }

        return [
            'success' => false,
            'error' => "Unsupported message type: {$message->message_type}"
        ];
    }
}
