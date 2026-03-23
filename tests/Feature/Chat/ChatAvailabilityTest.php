<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Services\Chat\ChatChannelAvailabilityService;
use App\Services\Chat\ChatConversationResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->company = Company::create([
                'name' => 'Test Company',
                'slug' => 'test-company',
                'primary_email' => 'test@company.com',
                'status' => 'trial',
            ]);

            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@user.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'company_id' => $this->company->id,
            ]);

            $this->account = WhatsAppAccount::create([
                'company_id' => $this->company->id,
                'access_token' => 'test-token',
                'waba_id' => '12345',
                'business_id' => '67890',
                'connection_status' => 'connected',
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
            throw $e;
        }
    }

    /** @test */
    public function service_returns_only_active_and_connected_numbers()
    {
        // 1. Active & Connected
        WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'Active Number',
            'phone_number_id' => 'PN1',
            'status' => 'active',
        ]);

        // 2. Inactive
        WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'Inactive Number',
            'phone_number_id' => 'PN2',
            'status' => 'inactive',
        ]);

        // 3. Disconnected Account
        $company2 = Company::create(['name' => 'Other Co', 'slug' => 'other-co', 'primary_email' => 'other@co.com']);
        $disconnectedAccount = WhatsAppAccount::create([
            'company_id' => $company2->id,
            'access_token' => 'another-token',
            'waba_id' => 'WABA2',
            'connection_status' => 'not_connected',
        ]);
        WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $disconnectedAccount->id,
            'display_name' => 'Disc Number',
            'phone_number_id' => 'PN3',
            'status' => 'active',
        ]);

        $service = app(ChatChannelAvailabilityService::class);
        $available = $service->getAvailableWhatsAppNumbersForUser($this->user);

        $this->assertCount(1, $available);
        $this->assertEquals('Active Number', $available->first()->display_name);
    }

    /** @test */
    public function resolver_creates_conversation_and_message_from_payload()
    {
        $localNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'My Channel',
            'phone_number_id' => 'PN123',
            'status' => 'active',
        ]);

        $payload = [
            'from' => '1234567890',
            'id' => 'WAMID.12345',
            'type' => 'text',
            'text' => ['body' => 'Hello World']
        ];

        $contact = [
            'profile' => ['name' => 'John Doe'],
            'wa_id' => '1234567890'
        ];

        $service = app(ChatConversationResolverService::class);
        $service->resolveAndProcessInboundMessage($localNumber, $payload, $contact);

        $this->assertDatabaseHas('conversations', [
            'company_id' => $this->company->id,
            'contact_phone' => '1234567890',
            'contact_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('conversation_messages', [
            'body' => 'Hello World',
            'direction' => 'inbound',
            'external_message_id' => 'WAMID.12345',
        ]);

        $conversation = Conversation::first();
        $this->assertEquals('Hello World', $conversation->last_message_preview);
    }
}
