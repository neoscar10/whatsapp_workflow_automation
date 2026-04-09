<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InboundMessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $conversationId,
        public int $messageId,
        public string $preview,
        public string $createdAt,
        public string $phoneNumber = '',
        public string $senderName = '',
        public string $direction = 'inbound'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("company.{$this->companyId}.chats"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.inbound.received';
    }

    public function broadcastWith(): array
    {
        return [
            'company_id' => $this->companyId,
            'conversation_id' => $this->conversationId,
            'message_id' => $this->messageId,
            'message_preview' => $this->preview,
            'created_at' => $this->createdAt,
            'phone_number' => $this->phoneNumber,
            'sender_name' => $this->senderName,
            'direction' => $this->direction,
        ];
    }
}
