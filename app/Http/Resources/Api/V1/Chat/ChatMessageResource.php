<?php

namespace App\Http\Resources\Api\V1\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'company_id' => $this->conversation->company_id,
            'contact_id' => $this->conversation->contact_id,
            'direction' => $this->direction,
            'message_type' => $this->message_type,
            'type' => $this->message_type, // Keeping for backward compatibility if web uses it
            'body' => $this->body,
            'media_url' => $this->media_url,
            'resolved_media_url' => $this->resolved_media_url,
            'media_meta' => $this->media_meta,
            'status' => $this->status,
            'time_label' => ($this->sent_at ?? $this->created_at)?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'sender_name' => $this->direction === 'inbound' 
                ? $this->conversation->contact_name 
                : ($this->sender->name ?? 'System'),
        ];
    }
}
