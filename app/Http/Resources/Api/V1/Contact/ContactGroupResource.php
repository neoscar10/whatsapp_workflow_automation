<?php

namespace App\Http\Resources\Api\V1\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'rules' => $this->rules,
            'contacts_count' => $this->whenCounted('contacts', $this->contacts_count),
            'resolved_count' => $this->resolved_count ?? ($this->relationLoaded('contacts') ? $this->contacts_count : 0),
            'member_count' => $this->resolved_count ?? ($this->relationLoaded('contacts') ? $this->contacts_count : 0),
        ];
    }
}
