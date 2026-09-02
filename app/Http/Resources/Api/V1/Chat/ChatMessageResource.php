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
            'status_icon' => $this->getStatusIcon($this->status),
            'status_color' => $this->getStatusColor($this->status),
            'failure_message' => $this->failure_message,
            'is_active' => (bool) ($this->conversation->is_session_active ?? false),
            'is_session_active' => (bool) ($this->conversation->is_session_active ?? false),
            'can_send_freeform' => (bool) ($this->conversation->is_session_active ?? false),
            'time_label' => ($this->sent_at ?? $this->created_at)?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'sender_name' => $this->direction === 'inbound' 
                ? $this->conversation->contact_name 
                : ($this->sender->name ?? 'System'),
        ];
    }

    private function getStatusIcon(?string $status): string
    {
        return match($status) {
            'read' => 'done_all',
            'delivered' => 'done_all',
            'sent' => 'check',
            'failed' => 'error',
            'pending' => 'schedule',
            default => 'schedule',
        };
    }

    private function getStatusColor(?string $status): string
    {
        return match($status) {
            'read' => '#38bdf8', // sky-400 (Double Blue Tick)
            'delivered' => '#94a3b8', // slate-400 (Double Grey Tick)
            'sent' => '#94a3b8', // slate-400 (Single Grey Tick)
            'failed' => '#ef4444', // red-500
            'pending' => '#94a3b8', // slate-400
            default => '#94a3b8',
        };
    }
}
