<?php

namespace App\Services\Contact;

use App\Models\Chat\Conversation;
use App\Models\Contact\Contact;
use App\Models\User;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactSyncService
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    /**
     * Backfill contacts from existing conversations.
     */
    public function backfillFromConversations(?int $companyId = null, bool $dryRun = false): array
    {
        $query = Conversation::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $conversations = $query->get();
        $stats = [
            'scanned' => $conversations->count(),
            'created' => 0,
            'updated' => 0,
            'linked' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($conversations as $conv) {
            try {
                if (empty($conv->contact_phone)) {
                    $stats['skipped']++;
                    continue;
                }

                $normalizedPhone = PhoneNumberNormalizer::normalize($conv->contact_phone);
                if (empty($normalizedPhone)) {
                    $stats['skipped']++;
                    continue;
                }

                if ($dryRun) {
                    $contact = Contact::where('company_id', $conv->company_id)
                        ->where('normalized_phone', $normalizedPhone)
                        ->first();
                    
                    if (!$contact) {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                    $stats['linked']++;
                    continue;
                }

                $contact = Contact::firstOrCreate(
                    [
                        'company_id' => $conv->company_id,
                        'normalized_phone' => $normalizedPhone,
                    ],
                    [
                        'name' => $conv->contact_name,
                        'phone' => $conv->contact_phone,
                        'avatar_url' => $conv->contact_avatar_url,
                        'source' => 'inbound_chat',
                        'whatsapp_phone_number_id' => $conv->whatsapp_phone_number_id,
                        'last_interaction_at' => $conv->last_message_at,
                        'last_inbound_at' => $conv->last_customer_message_at,
                    ]
                );

                if ($contact->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                    // Update fields if they were null
                    $contact->update([
                        'name' => $contact->name ?? $conv->contact_name,
                        'avatar_url' => $contact->avatar_url ?? $conv->contact_avatar_url,
                        'last_interaction_at' => max($contact->last_interaction_at, $conv->last_message_at),
                    ]);
                }

                $conv->update(['contact_id' => $contact->id]);
                $stats['linked']++;

            } catch (\Exception $e) {
                $stats['errors'][] = "Conv #{$conv->id}: " . $e->getMessage();
                Log::error("Contact Backfill Error", ['conv_id' => $conv->id, 'error' => $e->getMessage()]);
            }
        }

        return $stats;
    }

    /**
     * Sync a single conversation to a contact.
     */
    public function syncConversation(Conversation $conversation): ?Contact
    {
        try {
            $normalizedPhone = PhoneNumberNormalizer::normalize($conversation->contact_phone);
            
            if (empty($normalizedPhone)) return null;

            $contact = Contact::updateOrCreate(
                [
                    'company_id' => $conversation->company_id,
                    'normalized_phone' => $normalizedPhone,
                ],
                [
                    'name' => $contact->name ?? $conversation->contact_name, // Keep existing name if set
                    'phone' => $conversation->contact_phone,
                    'avatar_url' => $contact->avatar_url ?? $conversation->contact_avatar_url,
                    'last_interaction_at' => now(),
                    'last_inbound_at' => $conversation->last_customer_message_at ?? now(),
                ]
            );

            if ($conversation->contact_id !== $contact->id) {
                $conversation->update(['contact_id' => $contact->id]);
            }

            return $contact;
        } catch (\Exception $e) {
            Log::warning('Contact sync failed for conversation', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
