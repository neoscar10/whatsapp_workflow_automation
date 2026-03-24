<?php

namespace Tests\Feature\Chat;

use App\Events\Chat\InboundMessageReceived;
use App\Models\Chat\Conversation;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatConversationResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected \App\Models\WhatsApp\WhatsAppAccount $account;
    protected WhatsAppPhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@example.com'
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        $this->account = \App\Models\WhatsApp\WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'test-token',
            'waba_id' => 'test-waba-id',
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'phone_number_id' => 'test-phone-id',
            'phone_number' => '1234567890',
            'display_name' => 'Test Number',
        ]);
    }

    public function test_inbound_message_persistence_dispatches_broadcast_event()
    {
        Event::fake([InboundMessageReceived::class]);

        $service = app(ChatConversationResolverService::class);
        $service->resolveAndProcessInboundMessage($this->phoneNumber, [
            'from' => '0987654321',
            'id' => 'msg-realtime-123',
            'type' => 'text',
            'text' => ['body' => 'Hello Realtime'],
        ]);

        Event::assertDispatched(InboundMessageReceived::class, function ($event) {
            return $event->companyId === $this->company->id &&
                   $event->preview === 'Hello Realtime';
        });
    }

    public function test_company_channel_auth_allows_same_company_user()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => "private-company.{$this->company->id}.chats",
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(200);
    }

    public function test_company_channel_auth_blocks_different_company_user()
    {
        $otherCompany = Company::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'primary_email' => 'other@example.com'
        ]);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
        
        $this->actingAs($otherUser);

        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => "private-company.{$this->company->id}.chats",
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(403);
    }

    public function test_existing_chat_open_flow_still_works()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'Test Contact',
            'contact_phone' => '0987654321',
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Livewire\Web\Chats\ChatInboxPage::class, ['selectedConversationId' => $conversation->id])
            ->assertSet('selectedConversationId', $conversation->id)
            ->assertSee('Test Contact');
    }

    public function test_realtime_refresh_method_does_not_clear_selected_conversation_state()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'Test Contact',
            'contact_phone' => '0987654321',
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Livewire\Web\Chats\ChatInboxPage::class)
            ->call('selectConversation', $conversation->id)
            ->assertSet('selectedConversationId', $conversation->id)
            ->call('refreshChatDataAfterRealtimeEvent', ['conversation_id' => $conversation->id])
            ->assertSet('selectedConversationId', $conversation->id);
    }
}
