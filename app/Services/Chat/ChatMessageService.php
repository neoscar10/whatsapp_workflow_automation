<?php

namespace App\Services\Chat;

use App\Models\Chat\ConversationMessage;
use App\Models\User;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;

class ChatMessageService
{
    private ChatInboxService $inboxService;

    public function __construct(ChatInboxService $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Send a text message and persist local record.
     */
    public function sendTextMessage(User $user, int $conversationId, string $message): ?ConversationMessage
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return null;
        }

        // Persist local message
        $msg = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $message,
            'status' => 'pending',
            'sent_by_user_id' => $user->id,
            'sent_at' => now(),
        ]);

        // Update conversation summary
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => substr($message, 0, 50),
            'unread_count' => 0, // usually we clear unread if we reply
        ]);

        // TODO: Here, dispatch the WhatsApp outbound sending logic

        $msg->update(['status' => 'sent']); // Optimistic update for now

        // Broadcast events
        broadcast(new ChatMessageReceived($msg));
        broadcast(new ChatConversationUpdated($conversation));

        return $msg;
    }

    /**
     * Mark conversation as read.
     */
    public function markConversationRead(User $user, int $conversationId): void
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if ($conversation) {
            $conversation->update(['unread_count' => 0]);
        }
    }
}
