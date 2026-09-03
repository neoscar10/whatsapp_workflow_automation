<?php

namespace App\Events\Chat;

use App\Models\Chat\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("company.{$this->conversation->company_id}.chats"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        $lastMsg = $this->conversation->latestMessage;

        return [
            'id' => $this->conversation->id,
            'company_id' => $this->conversation->company_id,
            'contact_name' => $this->conversation->contact_name,
            'contact_phone' => $this->conversation->contact_phone,
            'status' => $this->conversation->status,
            'assignment_status' => $this->conversation->assignment_status,
            'preview' => $this->conversation->last_message_preview,
            'last_message_preview' => $this->conversation->last_message_preview,
            'unread_count' => $this->conversation->unread_count,
            'is_online' => (bool) $this->conversation->is_session_active,
            'is_session_active' => (bool) $this->conversation->is_session_active,
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
            'time_label' => $this->conversation->last_message_at?->diffForHumans(['short' => true]) ?? '',
            'last_message_at' => $this->conversation->last_message_at?->toDateTimeString(),
        ];
    }
}
