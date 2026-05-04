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
            'direction' => $this->direction,
            'type' => $this->message_type,
            'body' => $this->body,
            'media_url' => $this->resolved_media_url,
            'media_meta' => $this->media_meta,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'sender_name' => $this->sender?->name,
        ];
    }
}
