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
                'channels' => $this->availabilityService->getAvailableWhatsAppNumbersForUser($user)->map(function($ch) {
                    return ['id' => $ch->id, 'display_name' => $ch->display_name, 'phone_number' => $ch->phone_number];
                })->toArray(),
            ],
        ];
    }

    /**
     * Fetch conversations list for user's company.
     */
    public function getConversationListForUser(User $user, array $filters = []): Collection
    {
        // Failsafe Alignment: Ensure any conversations associated with user's company contacts or phone numbers match $user->company_id
        try {
            $userAccountIds = \App\Models\WhatsApp\WhatsAppAccount::where('company_id', $user->company_id)->pluck('id');
            if ($userAccountIds->isNotEmpty()) {
                $phoneIds = \App\Models\WhatsApp\WhatsAppPhoneNumber::whereIn('whatsapp_account_id', $userAccountIds)->pluck('id');
                if ($phoneIds->isNotEmpty()) {
                    Conversation::whereIn('whatsapp_phone_number_id', $phoneIds)
                        ->where('company_id', '!=', $user->company_id)
                        ->update(['company_id' => $user->company_id]);
                }
            }

            $userContactPhones = \App\Models\Contact\Contact::where('company_id', $user->company_id)->pluck('phone')->filter()->toArray();
            if (!empty($userContactPhones)) {
                $cleanContactPhones = array_map(fn($p) => preg_replace('/[^0-9]/', '', $p), $userContactPhones);
                $last10Phones = array_map(fn($p) => strlen($p) >= 10 ? substr($p, -10) : $p, $cleanContactPhones);

                $defaultPhone = $this->availabilityService->getDefaultWhatsAppNumberForUser($user);
                $alignmentPayload = ['company_id' => $user->company_id];
                if ($defaultPhone) {
                    $alignmentPayload['whatsapp_phone_number_id'] = $defaultPhone->id;
                }

                Conversation::where(function ($q) use ($userContactPhones, $last10Phones) {
                    $q->whereIn('contact_phone', $userContactPhones);
                    foreach ($last10Phones as $last10) {
                        if (strlen($last10) >= 7) {
                            $q->orWhere('contact_phone', 'like', '%' . $last10);
                        }
                    }
                })->where('company_id', '!=', $user->company_id)
                  ->update($alignmentPayload);
            }
        } catch (\Exception $e) {
            // Ignore alignment exceptions
        }

        // Failsafe Alignment: If any conversation for this company points to a WhatsApp phone number that does not belong to the company,
        // or if it's null, align it to the company's default WhatsApp phone number.
        try {
            $defaultPhone = $this->availabilityService->getDefaultWhatsAppNumberForUser($user);
            if ($defaultPhone) {
                $mismatchedIds = Conversation::where('company_id', $user->company_id)
                    ->where(function($q) use ($user) {
                        $q->whereNull('whatsapp_phone_number_id')
                          ->orWhereHas('whatsappPhoneNumber', function($sub) use ($user) {
                              $sub->where('company_id', '!=', $user->company_id);
                          });
                    })
                    ->pluck('id');

                if ($mismatchedIds->isNotEmpty()) {
                    Conversation::whereIn('id', $mismatchedIds)
                        ->update(['whatsapp_phone_number_id' => $defaultPhone->id]);
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }

        $query = Conversation::where('company_id', $user->company_id)
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC');

        if (!empty($filters['whatsapp_phone_number_id'])) {
            $query->where('whatsapp_phone_number_id', $filters['whatsapp_phone_number_id']);
        }

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
            } elseif ($filters['tab'] === 'active') {
                $query->where('last_customer_message_at', '>=', now()->subHours(24));
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

        $conversation = Conversation::where('company_id', $user->company_id)
            ->where('id', $conversationId)
            ->first();

        // Fallback: If requested conversation ID no longer exists (e.g. merged or invalid URL query param),
        // fallback to the most recent active conversation for the user's company so inbox is never stuck on dead ID
        if (!$conversation) {
            $conversation = Conversation::where('company_id', $user->company_id)
                ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
                ->first();
        }

        // Failsafe: Ensure conversation's whatsapp_phone_number_id belongs to the company
        if ($conversation) {
            $hasMismatch = false;
            if (!$conversation->whatsapp_phone_number_id) {
                $hasMismatch = true;
            } else {
                $phone = $conversation->whatsappPhoneNumber;
                if (!$phone || $phone->company_id !== $user->company_id) {
                    $hasMismatch = true;
                }
            }

            if ($hasMismatch) {
                $defaultPhone = $this->availabilityService->getDefaultWhatsAppNumberForUser($user);
                if ($defaultPhone) {
                    $conversation->update(['whatsapp_phone_number_id' => $defaultPhone->id]);
                    $conversation->load('whatsappPhoneNumber'); // Reload the relationship
                }
            }
        }

        return $conversation;
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
