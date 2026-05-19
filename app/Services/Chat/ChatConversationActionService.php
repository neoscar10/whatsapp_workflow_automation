<?php

namespace App\Services\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationNote;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;

class ChatConversationActionService
{
    public function __construct(
        protected ChatInboxService $inboxService,
        protected ChatChannelAvailabilityService $availabilityService
    ) {}

    /**
     * Start/Initiate a conversation with a contact.
     *
     * @param User $user
     * @param int $contactId
     * @return Conversation
     * @throws \Exception
     */
    public function startConversation(User $user, int $contactId): Conversation
    {
        $contact = \App\Models\Contact\Contact::where('company_id', $user->company_id)->findOrFail($contactId);

        if (!$contact->isMessageable()) {
            throw new \Exception('This contact is blocked or has opted out of messaging.');
        }

        // Determine which WhatsApp phone number to use:
        // 1. If the contact is associated with a specific active whatsapp_phone_number_id, use it if chat eligible.
        $whatsappPhoneNumber = null;
        if ($contact->whatsapp_phone_number_id) {
            $phoneNumber = WhatsAppPhoneNumber::find($contact->whatsapp_phone_number_id);
            if ($phoneNumber && $this->availabilityService->isNumberChatEligible($phoneNumber)) {
                $whatsappPhoneNumber = $phoneNumber;
            }
        }

        // 2. Otherwise, fall back to the default available number for the user's company.
        if (!$whatsappPhoneNumber) {
            $whatsappPhoneNumber = $this->availabilityService->getDefaultWhatsAppNumberForUser($user);
        }

        if (!$whatsappPhoneNumber) {
            throw new \Exception('No active WhatsApp phone number is connected or configured for your company.');
        }

        // Find or create the conversation record
        $conversation = Conversation::firstOrCreate(
            [
                'company_id' => $user->company_id,
                'contact_id' => $contact->id,
            ],
            [
                'whatsapp_phone_number_id' => $whatsappPhoneNumber->id,
                'contact_name' => $contact->name ?? $contact->phone,
                'contact_phone' => $contact->phone,
                'status' => 'open',
                'assignment_status' => 'unassigned',
            ]
        );

        // Ensure the conversation is open if it was closed
        if ($conversation->status !== 'open') {
            $conversation->update(['status' => 'open']);
        }

        return $conversation;
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
