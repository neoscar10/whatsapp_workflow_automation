<?php

namespace App\Services\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;
use App\Events\Chat\InboundMessageReceived;
use Illuminate\Support\Facades\Log;

class ChatConversationResolverService
{
    /**
     * Resolve an inbound WhatsApp message to a local conversation and store the message.
     *
     * @param WhatsAppPhoneNumber $localNumber
     * @param array $messageData (The 'message' object from the 'value' change)
     * @param array $contactData (The 'contact' object if available)
     * @return void
     */
    public function resolveAndProcessInboundMessage(WhatsAppPhoneNumber $localNumber, array $messageData, array $contactData = []): void
    {
        $fromPhone = $messageData['from']; // Customer's phone number
        $messageId = $messageData['id'];

        // 1. Find or create the conversation
        $conversation = Conversation::firstOrCreate(
            [
                'company_id' => $localNumber->company_id,
                'whatsapp_phone_number_id' => $localNumber->id,
                'contact_phone' => $fromPhone,
            ],
            [
                'contact_name' => $contactData['profile']['name'] ?? $fromPhone,
                'assignment_status' => 'unassigned',
            ]
        );

        // 2. Prepare message body based on type
        $type = $messageData['type'] ?? 'text';
        $body = '';

        if ($type === 'text') {
            $body = $messageData['text']['body'] ?? '';
        } elseif ($type === 'button') {
            $body = $messageData['button']['text'] ?? '[Button Clicked]';
        } elseif ($type === 'interactive') {
            $interactive = $messageData['interactive'];
            if ($interactive['type'] === 'button_reply') {
                $body = $interactive['button_reply']['title'] ?? '[Button Reply]';
            } elseif ($interactive['type'] === 'list_reply') {
                $body = $interactive['list_reply']['title'] ?? '[List Reply]';
            }
        } else {
            $body = "[Unsupported Message Type: {$type}]";
        }

        // 3. Prevent duplicate message processing if already existing
        if (ConversationMessage::where('external_message_id', $messageId)->exists()) {
            return;
        }

        // 4. Create the message
        $msg = $conversation->messages()->create([
            'external_message_id' => $messageId,
            'direction' => 'inbound',
            'message_type' => $type === 'text' ? 'text' : 'other',
            'body' => $body,
            'status' => 'received',
            'meta_payload' => $messageData,
            'sent_at' => now(), // Meta timestamp is in seconds, for now we use 'now'
        ]);

        // 5. Update conversation summary
        $conversation->update([
            'last_message_preview' => $body,
            'last_message_at' => now(),
            'last_customer_message_at' => now(), // WhatsApp 24h window trigger
            // Unread count tracking could go here
        ]);

        Log::debug('Realtime: Inbound message saved', [
            'conversation_id' => $conversation->id,
            'message_id' => $msg->id,
            'company_id' => $conversation->company_id,
        ]);

        // Broadcast events
        broadcast(new ChatMessageReceived($msg));
        broadcast(new ChatConversationUpdated($conversation));
        
        Log::debug('Realtime: Dispatching InboundMessageReceived event', [
            'channel' => "company.{$conversation->company_id}.chats",
            'event' => 'chat.inbound.received'
        ]);

        broadcast(new InboundMessageReceived(
            companyId: $conversation->company_id,
            conversationId: $conversation->id,
            messageId: $msg->id,
            preview: $body,
            createdAt: $msg->created_at->toDateTimeString(),
            phoneNumber: $conversation->contact_phone,
            senderName: $conversation->contact_name ?? '',
            direction: 'inbound'
        ));

        Log::debug('Realtime: Broadcast call finished');
    }
}
