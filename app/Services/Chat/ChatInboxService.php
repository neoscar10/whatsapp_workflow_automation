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
            if ($activeConversationModel = $this->getActiveConversationForUser($user, $selectedId)) {
                $activeConversation = [
                    'id' => $activeConversationModel->id,
                    'name' => $activeConversationModel->contact_name,
                    'phone' => $activeConversationModel->contact_phone,
                    'avatar_url' => $activeConversationModel->contact_avatar_url,
                    'location' => $activeConversationModel->contact_location,
                    'is_active' => true,
                ];

                $messages = $this->getMessagesForConversation($user, $activeConversationModel->id);
                $sidebarData = $this->getConversationSidebarData($user, $activeConversationModel->id);
            }
        }

        return [
            'conversations' => $conversations->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->contact_name,
                'phone' => $c->contact_phone,
                'avatar_url' => $c->contact_avatar_url,
                'preview' => $c->last_message_preview ?? 'No messages yet',
                'time_label' => $c->last_message_at ? $c->last_message_at->diffForHumans(short: true) : '',
                'unread_count' => $c->unread_count,
                'is_active' => true,
            ]),
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
     * Get messages for conversation properly shaped for UI.
     */
    public function getMessagesForConversation(User $user, int $conversationId): Collection
    {
        $conversation = $this->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return collect();
        }

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        return $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'direction' => $m->direction,
                'message_type' => $m->message_type,
                'body' => $m->body,
                'media_url' => $m->media_url,
                'status' => $m->status,
                'status_icon' => $this->getStatusIcon($m->status),
                'status_color' => $this->getStatusColor($m->status),
                'failure_message' => $m->failure_message,
                'time_label' => $m->sent_at ? $m->sent_at->format('H:i') : $m->created_at->format('H:i'),
                'card_title' => $m->media_meta['title'] ?? null,
                'card_heading' => $m->media_meta['heading'] ?? null,
                'card_subtext' => $m->media_meta['subtext'] ?? null,
                'card_button_text' => $m->media_meta['button_text'] ?? null,
            ];
        });
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

    private function getStatusIcon(?string $status): string
    {
        return match($status) {
            'read' => 'done_all',
            'delivered' => 'done_all',
            'sent' => 'check',
            'failed' => 'error',
            'pending' => 'schedule',
            default => 'schedule',
        };
    }

    private function getStatusColor(?string $status): string
    {
        return match($status) {
            'read' => 'text-blue-500',
            'delivered' => 'text-slate-400',
            'sent' => 'text-slate-400',
            'failed' => 'text-red-500',
            'pending' => 'text-slate-400',
            default => 'text-slate-400',
        };
    }
}
