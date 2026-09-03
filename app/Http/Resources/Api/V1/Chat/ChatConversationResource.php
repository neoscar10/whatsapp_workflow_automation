<?php

namespace App\Http\Resources\Api\V1\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastMsg = $this->latestMessage;

        return [
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'assignment_status' => $this->assignment_status,
            'assigned_user_id' => $this->assigned_user_id,
            'assigned_user_name' => $this->assignee?->name,
            'unread_count' => $this->unread_count,
            'is_online' => (bool) $this->is_session_active,
            'is_active' => (bool) $this->is_session_active,
            'is_session_active' => (bool) $this->is_session_active,
            'can_send_freeform' => (bool) $this->is_session_active,
            'last_customer_message_at' => $this->last_customer_message_at?->toIso8601String(),
            'last_message_preview' => $this->last_message_preview,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'last_message_status' => $lastMsg?->status,
            'last_message_direction' => $lastMsg?->direction,
            'last_message_type' => $lastMsg?->message_type,
            'last_message_ticks_state' => match($lastMsg?->status) {
                'read' => 'double_blue',
                'delivered' => 'double_grey',
                'sent' => 'single_grey',
                'failed' => 'failed',
                default => 'pending',
            },
            'last_message_ticks_count' => match($lastMsg?->status) {
                'read', 'delivered' => 2,
                'sent' => 1,
                default => 0,
            },
            'last_message_is_blue_ticks' => $lastMsg?->status === 'read',
            'last_message_status_icon' => match($lastMsg?->status) {
                'read' => 'done_all',
                'delivered' => 'done_all',
                'sent' => 'check',
                'failed' => 'error',
                'pending' => 'schedule',
                default => 'schedule',
            },
            'last_message_status_color' => match($lastMsg?->status) {
                'read' => '#38bdf8',
                'delivered' => '#94a3b8',
                'sent' => '#94a3b8',
                'failed' => '#ef4444',
                default => '#94a3b8',
            },
            'last_message' => $lastMsg ? [
                'id' => $lastMsg->id,
                'direction' => $lastMsg->direction,
                'message_type' => $lastMsg->message_type,
                'body' => $lastMsg->body,
                'status' => $lastMsg->status,
                'ticks_state' => match($lastMsg->status) {
                    'read' => 'double_blue',
                    'delivered' => 'double_grey',
                    'sent' => 'single_grey',
                    'failed' => 'failed',
                    default => 'pending',
                },
                'ticks_count' => match($lastMsg->status) {
                    'read', 'delivered' => 2,
                    'sent' => 1,
                    default => 0,
                },
                'is_blue_ticks' => $lastMsg->status === 'read',
                'status_icon' => match($lastMsg->status) {
                    'read' => 'done_all',
                    'delivered' => 'done_all',
                    'sent' => 'check',
                    'failed' => 'error',
                    'pending' => 'schedule',
                    default => 'schedule',
                },
                'status_color' => match($lastMsg->status) {
                    'read' => '#38bdf8',
                    'delivered' => '#94a3b8',
                    'sent' => '#94a3b8',
                    'failed' => '#ef4444',
                    default => '#94a3b8',
                },
                'created_at' => $lastMsg->created_at?->toIso8601String(),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
