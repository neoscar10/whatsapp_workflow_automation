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
use Illuminate\Support\Facades\Event;
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
        
        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company-' . uniqid(),
            'primary_email' => 'test-company@example.com',
            'wallet_balance' => 100.00,
        ]);
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
            'display_name' => 'Test',
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
        Event::fake([
            ChatMessageReceived::class,
            ChatConversationUpdated::class,
        ]);

        $service = app(ChatMessageService::class);
        $service->sendTextMessage($this->user, $this->conversation->id, 'Hello World');

        Event::assertDispatched(ChatMessageReceived::class, function ($event) {
            return $event->message->body === 'Hello World' &&
                   $event->broadcastOn()[0]->name === "private-company.{$this->company->id}.conversation.{$this->conversation->id}";
        });

        Event::assertDispatched(ChatConversationUpdated::class, function ($event) {
            $channels = collect($event->broadcastOn())->map(fn($c) => $c->name)->all();
            return $event->conversation->id === $this->conversation->id &&
                   in_array("private-company.{$this->company->id}.chats", $channels) &&
                   in_array("private-company.{$this->company->id}.conversation.{$this->conversation->id}", $channels);
        });
    }

    public function test_private_channel_authorization()
    {
        // Auth success for same company
        $response = $this->actingAs($this->user)->post('/broadcasting/auth', [
            'channel_name' => 'private-company.' . $this->company->id . '.chats',
            'socket_id' => '1234.5678',
        ]);
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->post('/broadcasting/auth', [
            'channel_name' => 'private-company.' . $this->company->id . '.conversation.' . $this->conversation->id,
            'socket_id' => '1234.5678',
        ]);
        $response->assertStatus(200);

        // Auth failure for other company
        $otherCompany = Company::create([
            'name' => 'Other',
            'slug' => 'other-company-' . uniqid(),
            'primary_email' => 'other-company@example.com'
        ]);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($otherUser)->post('/broadcasting/auth', [
            'channel_name' => 'private-company.' . $this->company->id . '.chats',
            'socket_id' => '1234.5678',
        ]);
        $response->assertStatus(403);

        $response = $this->actingAs($otherUser)->post('/broadcasting/auth', [
            'channel_name' => 'private-company.' . $this->company->id . '.conversation.' . $this->conversation->id,
            'socket_id' => '1234.5678',
        ]);
        $response->assertStatus(403);
    }
}
