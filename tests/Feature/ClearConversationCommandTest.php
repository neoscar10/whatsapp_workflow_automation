<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearConversationCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected WhatsAppPhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Clear Test Co',
            'slug' => 'clear-test-co',
            'primary_email' => 'cleartest@co.com',
        ]);

        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        $account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'name' => 'Clear Acc',
            'waba_id' => '123456',
            'access_token' => 'token'
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => '998877',
            'phone_number' => '+1234567890',
            'display_name' => 'Phone',
            'status' => 'active'
        ]);
    }

    public function test_can_clear_conversation_via_artisan_command()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
            'contact_name' => 'Test User',
            'contact_phone' => '+1234567890',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'message_type' => 'text',
            'body' => 'Hello test message',
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseHas('conversation_messages', ['conversation_id' => $conversation->id]);

        $this->artisan('chat:clear-conversation', [
            'id' => $conversation->id,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('conversation_messages', ['conversation_id' => $conversation->id]);
    }

    public function test_returns_failure_if_conversation_does_not_exist()
    {
        $this->artisan('chat:clear-conversation', [
            'id' => 999999,
            '--force' => true,
        ])->assertExitCode(1);
    }
}
