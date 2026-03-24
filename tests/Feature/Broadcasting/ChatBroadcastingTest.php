<?php

namespace Tests\Feature\Broadcasting;

use App\Events\Chat\ChatConversationUpdated;
use App\Events\Chat\ChatMessageReceived;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatMessageService;
use App\Services\Chat\ChatInboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class ChatBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create(['name' => 'Test Company']);
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
        
        $phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'phone_number_id' => '12345' . uniqid(),
            'display_phone_number' => '12345',
            'verified_name' => 'Test',
        ]);

        $this->conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_phone' => '1234567890',
            'contact_name' => 'Customer',
        ]);
    }

    public function test_outbound_message_dispatches_realtime_events()
    {
        Broadcast::fake();

        $service = app(ChatMessageService::class);
        $service->sendTextMessage($this->user, $this->conversation->id, 'Hello World');

        Broadcast::assertDispatched(ChatMessageReceived::class, function ($event) {
            return $event->message->body === 'Hello World' &&
                   $event->broadcastOn()[0]->name === "private-company.{$this->company->id}.conversation.{$this->conversation->id}";
        });

        Broadcast::assertDispatched(ChatConversationUpdated::class, function ($event) {
            return $event->conversation->id === $this->conversation->id &&
                   $event->broadcastOn()[0]->name === "private-company.{$this->company->id}.chats";
        });
    }

    public function test_private_channel_authorization()
    {
        // Auth success for same company
        $this->assertTrue(
            Broadcast::auth($this->user, 'company.' . $this->company->id . '.chats')
        );

        $this->assertTrue(
            Broadcast::auth($this->user, 'company.' . $this->company->id . '.conversation.' . $this->conversation->id)
        );

        // Auth failure for other company
        $otherCompany = Company::create(['name' => 'Other']);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $this->assertFalse(
            Broadcast::auth($otherUser, 'company.' . $this->company->id . '.chats')
        );

        $this->assertFalse(
            Broadcast::auth($otherUser, 'company.' . $this->company->id . '.conversation.' . $this->conversation->id)
        );
    }
}
