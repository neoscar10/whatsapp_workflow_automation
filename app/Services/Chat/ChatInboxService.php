<?php

namespace App\Services\Chat;

use App\Models\Chat\Conversation;
use App\Models\User;
use Illuminate\Support\Collection;

class ChatInboxService
{
    public function __construct(
        protected ChatChannelAvailabilityService $availabilityService
    ) {}
    /**
     * Get inbox data for user.
     */
    public function getInboxDataForUser(User $user, array $filters = []): array
    {
        $conversations = $this->getConversationListForUser($user, $filters);
        
        $selectedId = $filters['selected_conversation_id'] ?? null;
        $activeConversation = null;
        $messages = collect();
        $sidebarData = [];

        if ($selectedId) {
            $activeConversation = $this->getActiveConversationForUser($user, $selectedId);
            if ($activeConversation) {
                $messages = $this->getMessagesForConversation($user, $activeConversation->id);
                $sidebarData = $this->getConversationSidebarData($user, $activeConversation->id);
            }
        }

        return [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'sidebarData' => $sidebarData,
            'has_conversations' => $conversations->isNotEmpty(),
            'show_empty_state' => $activeConversation === null,
            'channel_availability' => [
                'has_available_channels' => $this->availabilityService->getAvailableWhatsAppNumbersForUser($user)->isNotEmpty(),
                'available_count' => $this->availabilityService->getAvailableWhatsAppNumbersForUser($user)->count(),
                'default_channel' => $this->availabilityService->getDefaultWhatsAppNumberForUser($user),
            ],
        ];
    }

    /**
     * Fetch conversations list for user's company.
     */
    public function getConversationListForUser(User $user, array $filters = []): Collection
    {
        $query = Conversation::where('company_id', $user->company_id)
            ->orderBy('last_message_at', 'desc');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('contact_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('contact_phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['tab'])) {
            if ($filters['tab'] === 'assigned') {
                $query->where('assignment_status', 'assigned')
                      ->where('assigned_user_id', $user->id);
            } elseif ($filters['tab'] === 'unassigned') {
                $query->where('assignment_status', 'unassigned');
            }
        }

        return $query->get();
    }

    /**
     * Get active conversation model.
     */
    public function getActiveConversationForUser(User $user, ?int $conversationId = null): ?Conversation
    {
        if (!$conversationId) {
            return null;
        }

        return Conversation::where('company_id', $user->company_id)
            ->where('id', $conversationId)
            ->first();
    }

    /**
     * Get messages for conversation.
     */
    public function getMessagesForConversation(User $user, int $conversationId): Collection
    {
        $conversation = $this->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return collect();
        }

        return $conversation->messages()->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get right panel data.
     */
    public function getConversationSidebarData(User $user, int $conversationId): array
    {
        $conversation = $this->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return [];
        }

        $assignee = $conversation->assignee;

        return [
            'labels' => $conversation->labels ?? [],
            'notes' => $conversation->notes()->with('user')->orderBy('created_at', 'desc')->get(),
            'assignment' => $assignee ? [
                'id' => $assignee->id,
                'name' => $assignee->name,
                'assigned_at' => $conversation->assigned_at?->diffForHumans() ?? 'recently',
            ] : null,
        ];
    }
}
