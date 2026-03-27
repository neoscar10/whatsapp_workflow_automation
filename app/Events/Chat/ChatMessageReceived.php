<?php

namespace App\Events\Chat;

use App\Models\Chat\ConversationMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConversationMessage $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("company.{$this->message->conversation->company_id}.conversation.{$this->message->conversation_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'company_id' => $this->message->conversation->company_id,
            'direction' => $this->message->direction,
            'message_type' => $this->message->message_type,
            'body' => $this->message->body,
            'media_url' => $this->message->media_url,
            'resolved_media_url' => $this->message->resolved_media_url,
            'status' => $this->message->status,
            'time_label' => $this->message->sent_at?->format('H:i') ?? now()->format('H:i'),
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
