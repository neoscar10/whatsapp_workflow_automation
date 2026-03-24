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
        return [
            'id' => $this->conversation->id,
            'company_id' => $this->conversation->company_id,
            'preview' => $this->conversation->last_message_preview,
            'unread_count' => $this->conversation->unread_count,
            'time_label' => $this->conversation->last_message_at?->diffForHumans(['short' => true]) ?? '',
            'last_message_at' => $this->conversation->last_message_at?->toDateTimeString(),
        ];
    }
}
