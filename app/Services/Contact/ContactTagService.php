<?php

namespace App\Services\Contact;

use App\Models\Contact\ContactTag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContactTagService
{
    /**
     * List tags for a company with optional filtering.
     */
    public function listForCompany(int $companyId, array $filters = []): Collection
    {
        $query = ContactTag::where('company_id', $companyId)
            ->withCount('contacts');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new tag.
     */
    public function create(User $actor, array $data): ContactTag
    {
        $slug = Str::slug($data['name']);

        if (ContactTag::where('company_id', $actor->company_id)->where('slug', $slug)->exists()) {
            throw new \Exception("A tag with this name already exists.");
        }

        return ContactTag::create([
            'company_id' => $actor->company_id,
            'name' => $data['name'],
            'slug' => $slug,
            'color' => $data['color'] ?? '#3b82f6',
            'description' => $data['description'] ?? null,
            'created_by_user_id' => $actor->id,
        ]);
    }

    /**
     * Update an existing tag.
     */
    public function update(User $actor, ContactTag $tag, array $data): ContactTag
    {
        if ($tag->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to tag.");
        }

        if (isset($data['name'])) {
            $slug = Str::slug($data['name']);
            if (ContactTag::where('company_id', $actor->company_id)->where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
                throw new \Exception("A tag with this name already exists.");
            }
            $data['slug'] = $slug;
        }

        $tag->update($data);

        return $tag;
    }

    /**
     * Delete a tag.
     */
    public function delete(User $actor, ContactTag $tag): void
    {
        if ($tag->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to tag.");
        }

        // Pivot cleanup is handled by cascade or manual sync if needed
        $tag->delete();
    }

    /**
     * Assign a tag to multiple contacts.
     */
    public function assignToContacts(User $actor, ContactTag $tag, array $contactIds): void
    {
        if ($tag->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to tag.");
        }

        $contacts = \App\Models\Contact\Contact::where('company_id', $actor->company_id)
            ->whereIn('id', $contactIds)
            ->get();

        foreach ($contacts as $contact) {
            $contact->tags()->syncWithoutDetaching([$tag->id]);
        }
    }

    /**
     * Remove a tag from multiple contacts.
     */
    public function removeFromContacts(User $actor, ContactTag $tag, array $contactIds): void
    {
        if ($tag->company_id !== $actor->company_id) {
            throw new \Exception("Unauthorized access to tag.");
        }

        $contacts = \App\Models\Contact\Contact::where('company_id', $actor->company_id)
            ->whereIn('id', $contactIds)
            ->get();

        foreach ($contacts as $contact) {
            $contact->tags()->detach($tag->id);
        }
    }

    /**
     * Ensure tags exist by name and return their IDs.
     */
    public function ensureByNames(User $actor, array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $slug = Str::slug($name);
            $tag = ContactTag::where('company_id', $actor->company_id)->where('slug', $slug)->first();

            if (!$tag) {
                $tag = $this->create($actor, ['name' => $name]);
            }

            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }
}
