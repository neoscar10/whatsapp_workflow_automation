<?php

namespace App\Http\Resources\Api\V1\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'company_id' => $this->company_id,
            'whatsapp_phone_number_id' => $this->whatsapp_phone_number_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'normalized_phone' => $this->normalized_phone,
            'avatar_url' => $this->avatar_url,
            'source' => $this->source,
            'status' => $this->status,
            'has_opted_in' => $this->has_opted_in,
            'opted_in_at' => $this->opted_in_at?->toDateTimeString(),
            'opted_in_source' => $this->opted_in_source,
            'opted_out_at' => $this->opted_out_at?->toDateTimeString(),
            'do_not_message' => $this->do_not_message,
            'last_interaction_at' => $this->last_interaction_at?->toDateTimeString(),
            'last_inbound_at' => $this->last_inbound_at?->toDateTimeString(),
            'last_outbound_at' => $this->last_outbound_at?->toDateTimeString(),
            'notes' => $this->notes,
            'custom_fields' => $this->custom_fields,
            'tags' => ContactTagResource::collection($this->whenLoaded('tags')),
            'groups' => ContactGroupResource::collection($this->whenLoaded('groups')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
