<?php

namespace App\Services\Contact;

use App\Models\Contact\ContactGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContactGroupService
{
    /**
     * Search contacts that can be added to a group.
     */
    public function searchAvailableContactsForGroup(User $actor, ContactGroup $group, array $filters = [])
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        $query = \App\Models\Contact\Contact::forCompany($actor->company_id)
            ->whereDoesntHave('groups', function ($q) use ($group) {
                $q->where('contact_groups.id', $group->id);
            });

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->paginate($filters['per_page'] ?? 15, ['*'], $filters['page_name'] ?? 'page');
    }

    /**
     * List current members of a group.
     */
    public function getGroupMembers(User $actor, ContactGroup $group, array $filters = [])
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        $query = $group->contacts();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->paginate($filters['per_page'] ?? 15, ['*'], $filters['page_name'] ?? 'page');
    }

    /**
     * Add multiple contacts to a group.
     */
    public function addContactsToGroup(User $actor, ContactGroup $group, array $contactIds): array
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        if ($group->type !== 'static') {
            throw new \Exception("Dynamic segments are controlled by rules and cannot have manual contact assignments.");
        }

        // Validate contacts belong to the company
        $validContacts = \App\Models\Contact\Contact::forCompany($actor->company_id)
            ->whereIn('id', $contactIds)
            ->get();

        $invalidCount = count($contactIds) - $validContacts->count();
        
        // Find existing members among valid contacts to count them as skipped
        $existingMemberIds = $group->contacts()
            ->whereIn('contact_id', $validContacts->pluck('id'))
            ->pluck('contact_id')
            ->toArray();

        $skippedCount = count($existingMemberIds);
        $addedIds = $validContacts->pluck('id')->diff($existingMemberIds);

        $group->contacts()->syncWithoutDetaching($addedIds);

        return [
            'group_id' => $group->id,
            'added_count' => $addedIds->count(),
            'skipped_existing_count' => $skippedCount,
            'invalid_count' => $invalidCount,
            'member_count' => $group->contacts()->count(),
        ];
    }

    /**
     * Remove multiple contacts from a group.
     */
    public function removeContactsFromGroup(User $actor, ContactGroup $group, array $contactIds): array
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        if ($group->type !== 'static') {
            throw new \Exception("Dynamic segments are controlled by rules and cannot have manual contact assignments.");
        }

        $removedCount = $group->contacts()->detach($contactIds);

        return [
            'group_id' => $group->id,
            'removed_count' => $removedCount,
            'member_count' => $group->contacts()->count(),
        ];
    }

    /**
     * List groups for a company with optional filtering.
     */
    public function listForCompany(int $companyId, array $filters = []): Collection
    {
        $query = ContactGroup::where('company_id', $companyId)
            ->withCount('contacts');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $groups = $query->orderBy('name')->get();

        // For dynamic groups, we need to calculate the live count if not already doing so via a resolved table
        foreach ($groups as $group) {
            if ($group->type === 'dynamic') {
                // Resolved count is calculated via segment service
                $group->resolved_count = app(\App\Services\Contact\ContactSegmentRuleService::class)->count(auth()->user(), $group->rules ?? []);
            } else {
                $group->resolved_count = $group->contacts_count;
            }
        }

        return $groups;
    }

    /**
     * Create a new group.
     */
    public function create(User $actor, array $data): ContactGroup
    {
        $slug = Str::slug($data['name']);

        if (ContactGroup::where('company_id', $actor->company_id)->where('slug', $slug)->exists()) {
            throw new \Exception("A group with this name already exists.");
        }

        return ContactGroup::create([
            'company_id' => $actor->company_id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'static',
            'rules' => $data['rules'] ?? null,
            'created_by_user_id' => $actor->id,
        ]);
    }

    /**
     * Update an existing group.
     */
    public function update(User $actor, ContactGroup $group, array $data): ContactGroup
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        if (isset($data['name'])) {
            $slug = Str::slug($data['name']);
            if (ContactGroup::where('company_id', $actor->company_id)->where('slug', $slug)->where('id', '!=', $group->id)->exists()) {
                throw new \Exception("A group with this name already exists.");
            }
            $data['slug'] = $slug;
        }

        $group->update($data);

        return $group;
    }

    /**
     * Delete a group.
     */
    public function delete(User $actor, ContactGroup $group): void
    {
        if ($group->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to group.");
        }

        $group->delete();
    }

    /**
     * Attach contacts to a static group.
     */
    public function attachContacts(User $actor, ContactGroup $group, array $contactIds): void
    {
        if ($group->type !== 'static') {
            throw new \Exception("Dynamic segments are controlled by rules and cannot have manual contact assignments.");
        }

        $contacts = \App\Models\Contact\Contact::where('company_id', $actor->company_id)
            ->whereIn('id', $contactIds)
            ->get();

        $group->contacts()->syncWithoutDetaching($contacts->pluck('id'));
    }

    /**
     * Detach contacts from a static group.
     */
    public function detachContacts(User $actor, ContactGroup $group, array $contactIds): void
    {
        if ($group->type !== 'static') {
            throw new \Exception("Dynamic segments are controlled by rules and cannot have manual contact assignments.");
        }

        $group->contacts()->detach($contactIds);
    }

    /**
     * Ensure groups exist by name and return their IDs.
     */
    public function ensureByNames(User $actor, array $groupNames): array
    {
        $groupIds = [];

        foreach ($groupNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $slug = Str::slug($name);
            $group = ContactGroup::where('company_id', $actor->company_id)->where('slug', $slug)->first();

            if (!$group) {
                $group = $this->create($actor, ['name' => $name, 'type' => 'static']);
            }

            $groupIds[] = $group->id;
        }

        return $groupIds;
    }
}
