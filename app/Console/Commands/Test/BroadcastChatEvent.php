<?php

namespace App\Console\Commands\Test;

use App\Events\Chat\InboundMessageReceived;
use App\Models\Chat\Conversation;
use Illuminate\Console\Command;

class BroadcastChatEvent extends Command
{
    protected $signature = 'test:broadcast-chat {conversation_id} {message=Hello from CLI}';
    protected $description = 'Broadcast a test inbound message event for a specific conversation';

    public function handle()
    {
        $conversation = Conversation::find($this->argument('conversation_id'));

        if (!$conversation) {
            $this->error('Conversation not found');
            return 1;
        }

        $this->info("Broadcasting to company.{$conversation->company_id}.chats...");

        broadcast(new InboundMessageReceived(
            companyId: $conversation->company_id,
            conversationId: $conversation->id,
            messageId: 0,
            preview: $this->argument('message'),
            createdAt: now()->toDateTimeString(),
            phoneNumber: $conversation->contact_phone,
            senderName: $conversation->contact_name,
            direction: 'inbound'
        ));

        $this->info('Event dispatched successfully!');
        
        return 0;
    }
}
