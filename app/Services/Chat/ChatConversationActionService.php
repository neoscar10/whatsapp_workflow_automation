<?php

namespace App\Services\Chat;

use App\Models\Chat\ConversationNote;
use App\Models\User;

class ChatConversationActionService
{
    private ChatInboxService $inboxService;

    public function __construct(ChatInboxService $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Save a private team note.
     */
    public function savePrivateNote(User $user, int $conversationId, string $note): ?ConversationNote
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return null;
        }

        return $conversation->notes()->create([
            'user_id' => $user->id,
            'note' => $note,
        ]);
    }

    /**
     * Close a conversation.
     */
    public function closeConversation(User $user, int $conversationId): void
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if ($conversation) {
            $conversation->update(['status' => 'closed']);
        }
    }

    /**
     * Reopen a conversation.
     */
    public function reopenConversation(User $user, int $conversationId): void
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if ($conversation) {
            $conversation->update(['status' => 'open']);
        }
    }

    /**
     * Assign a conversation to an eligible agent within the same company.
     */
    public function assignConversation(User $actor, int $conversationId, int $agentId): array
    {
        $conversation = $this->inboxService->getActiveConversationForUser($actor, $conversationId);
        if (!$conversation) {
            throw new \Exception('Conversation not found or access denied.');
        }

        $agent = User::where('company_id', $actor->company_id)->find($agentId);
        if (!$agent) {
            throw new \Exception('Invalid agent selected or out of scope.');
        }

        $conversation->update([
            'assigned_user_id' => $agent->id,
            'assigned_at' => now(),
            'assignment_status' => 'assigned',
        ]);

        return $this->getAssignmentSummary($actor, $conversationId);
    }

    /**
     * Get assignment summary for sidebar UI freshness.
     */
    public function getAssignmentSummary(User $user, int $conversationId): array
    {
        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation || !$conversation->assigned_user_id) {
            return [];
        }

        $agent = $conversation->assignee;

        return [
            'assigned_user_id' => $agent->id,
            'assigned_user_name' => $agent->name,
            'assigned_user_avatar_url' => null,
            'assigned_at' => $conversation->assigned_at?->toIso8601String(),
            'assignment_status' => $conversation->assignment_status,
        ];
    }
}
