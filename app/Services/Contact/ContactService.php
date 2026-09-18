<?php

namespace App\Services\Contact;

use App\Models\Contact\Contact;
use App\Models\User;
use App\Models\Chat\Conversation;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactService
{
    /**
     * List contacts for a company with filters.
     */
    public function listForCompany(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Contact::forCompany($companyId)
            ->with(['tags', 'groups'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['has_opted_in'])) {
            $query->where('has_opted_in', $filters['has_opted_in']);
        }

        if (isset($filters['do_not_message'])) {
            $query->where('do_not_message', $filters['do_not_message']);
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('contact_tags.id', $filters['tag_id']);
            });
        }

        if (!empty($filters['group_id'])) {
            $query->whereHas('groups', function ($q) use ($filters) {
                $q->where('contact_groups.id', $filters['group_id']);
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Find a contact for a company.
     */
    public function findForCompany(int $companyId, int $contactId): Contact
    {
        return Contact::forCompany($companyId)->findOrFail($contactId);
    }

    /**
     * Create a new contact.
     */
    public function create(User $actor, array $data, ?int $companyId = null): Contact
    {
        $targetCompanyId = $companyId ?: $actor->company_id;
        if (!$targetCompanyId) {
            throw new \Exception("Company context is required to create a contact.");
        }

        $cleanPhone = PhoneNumberNormalizer::clean($data['phone']);
        $normalizedPhone = PhoneNumberNormalizer::normalize($cleanPhone);

        if (Contact::where('company_id', $targetCompanyId)->where('normalized_phone', $normalizedPhone)->exists()) {
            throw new \Exception("A contact with this phone number already exists.");
        }

        return DB::transaction(function () use ($actor, $targetCompanyId, $data, $cleanPhone, $normalizedPhone) {
            $contact = Contact::create([
                'company_id' => $targetCompanyId,
                'whatsapp_phone_number_id' => $data['whatsapp_phone_number_id'] ?? null,
                'name' => $data['name'] ?? null,
                'phone' => $cleanPhone,
                'normalized_phone' => $normalizedPhone,
                'avatar_url' => $data['avatar_url'] ?? null,
                'source' => $data['source'] ?? 'manual',
                'status' => $data['status'] ?? 'active',
                'has_opted_in' => $data['has_opted_in'] ?? false,
                'opted_in_at' => ($data['has_opted_in'] ?? false) ? ($data['opted_in_at'] ?? now()) : null,
                'opted_in_source' => $data['opted_in_source'] ?? null,
                'do_not_message' => $data['do_not_message'] ?? false,
                'notes' => $data['notes'] ?? null,
                'custom_fields' => $data['custom_fields'] ?? null,
                'created_by_user_id' => $actor->id,
            ]);

            if (!empty($data['tag_ids'])) {
                $contact->tags()->sync($data['tag_ids']);
            }

            if (!empty($data['group_ids'])) {
                $contact->groups()->sync($data['group_ids']);
            }

            return $contact;
        });
    }

    /**
     * Update an existing contact.
     */
    public function update(User $actor, Contact $contact, array $data): Contact
    {
        $isSuperAdmin = $actor->role === 'super_admin' || ($actor->is_super_admin ?? false);
        if (!$isSuperAdmin && $contact->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to contact.");
        }

        $cleanPhone = isset($data['phone']) ? PhoneNumberNormalizer::clean($data['phone']) : $contact->phone;
        $normalizedPhone = isset($data['phone']) ? PhoneNumberNormalizer::normalize($cleanPhone) : $contact->normalized_phone;

        if (isset($data['phone']) && $normalizedPhone !== $contact->normalized_phone) {
            if (Contact::where('company_id', $contact->company_id)->where('normalized_phone', $normalizedPhone)->where('id', '!=', $contact->id)->exists()) {
                throw new \Exception("A contact with this phone number already exists.");
            }
        }

        return DB::transaction(function () use ($actor, $contact, $data, $cleanPhone, $normalizedPhone) {
            $updateData = [
                'name' => $data['name'] ?? $contact->name,
                'phone' => $cleanPhone,
                'normalized_phone' => $normalizedPhone,
                'avatar_url' => $data['avatar_url'] ?? $contact->avatar_url,
                'status' => $data['status'] ?? $contact->status,
                'notes' => $data['notes'] ?? $contact->notes,
                'custom_fields' => $data['custom_fields'] ?? $contact->custom_fields,
                'updated_by_user_id' => $actor->id,
            ];

            if (isset($data['has_opted_in'])) {
                $updateData['has_opted_in'] = $data['has_opted_in'];
                if ($data['has_opted_in'] && !$contact->has_opted_in) {
                    $updateData['opted_in_at'] = now();
                    $updateData['opted_in_source'] = $data['opted_in_source'] ?? 'manual_update';
                    $updateData['do_not_message'] = false;
                    $updateData['opted_out_at'] = null;
                }
            }

            if (isset($data['do_not_message'])) {
                $updateData['do_not_message'] = $data['do_not_message'];
                if ($data['do_not_message'] && !$contact->do_not_message) {
                    $updateData['opted_out_at'] = now();
                }
            }

            $contact->update($updateData);

            if (isset($data['tag_ids'])) {
                $contact->tags()->sync($data['tag_ids']);
            }

            if (isset($data['group_ids'])) {
                $contact->groups()->sync($data['group_ids']);
            }

            return $contact;
        });
    }

    /**
     * Attach groups to a contact.
     */
    public function attachGroups(User $actor, Contact $contact, array $groupIds): Contact
    {
        $contact->groups()->syncWithoutDetaching($groupIds);
        return $contact->fresh(['tags', 'groups']);
    }

    /**
     * Detach a group from a contact.
     */
    public function detachGroup(User $actor, Contact $contact, int $groupId): Contact
    {
        $contact->groups()->detach($groupId);
        return $contact->fresh(['tags', 'groups']);
    }

    /**
     * Delete a contact.
     */
    public function delete(User $actor, Contact $contact): void
    {
        $isSuperAdmin = $actor->role === 'super_admin' || ($actor->is_super_admin ?? false);
        if (!$isSuperAdmin && $contact->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to contact.");
        }

        $contact->delete();
    }

    /**
     * Mark a contact as opted out.
     */
    public function markOptedOut(User $actor, Contact $contact, ?string $reason = null): void
    {
        $this->update($actor, $contact, [
            'do_not_message' => true,
            'notes' => $contact->notes . "\nOpted out at " . now()->toDateTimeString() . ($reason ? ": {$reason}" : ""),
        ]);
    }

    /**
     * Mark a contact as opted in.
     */
    public function markOptedIn(User $actor, Contact $contact, ?string $source = null): void
    {
        $this->update($actor, $contact, [
            'has_opted_in' => true,
            'opted_in_source' => $source ?? 'manual_action',
        ]);
    }

    /**
     * Link a conversation to a contact.
     */
    public function linkConversation(Contact $contact, Conversation $conversation): void
    {
        if ($contact->company_id !== $conversation->company_id) {
            throw new \Exception("Company mismatch between contact and conversation.");
        }

        $conversation->update(['contact_id' => $contact->id]);
    }

    /**
     * Bulk assign tags to contacts.
     */
    public function bulkAssignTags(User $actor, array $contactIds, array $tagIds): void
    {
        $contacts = Contact::forCompany($actor->company_id)->whereIn('id', $contactIds)->get();
        
        foreach ($contacts as $contact) {
            $contact->tags()->syncWithoutDetaching($tagIds);
        }
    }

    /**
     * Bulk assign groups to contacts.
     */
    public function bulkAssignGroups(User $actor, array $contactIds, array $groupIds): void
    {
        $contacts = Contact::forCompany($actor->company_id)->whereIn('id', $contactIds)->get();
        
        foreach ($contacts as $contact) {
            $contact->groups()->syncWithoutDetaching($groupIds);
        }
    }

    /**
     * Bulk delete contacts.
     */
    public function bulkDelete(User $actor, array $contactIds): void
    {
        Contact::forCompany($actor->company_id)->whereIn('id', $contactIds)->delete();
    }

    /**
     * Get aggregate statistics for a company's contacts.
     */
    public function getCompanyStats(int $companyId): array
    {
        return [
            'total' => Contact::forCompany($companyId)->count(),
            'opted_in' => Contact::forCompany($companyId)->where('has_opted_in', true)->count(),
            'recent' => Contact::forCompany($companyId)->where('last_interaction_at', '>', now()->subDays(7))->count(),
            'blocked' => Contact::forCompany($companyId)
                ->where(function($q) {
                    $q->where('status', 'blocked')
                      ->orWhere('do_not_message', true);
                })->count(),
        ];
    }
}
