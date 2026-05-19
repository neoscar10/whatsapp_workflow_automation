<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\Chat\Conversation;
use App\Services\Chat\ChatConversationActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Web\Chats\ChatInboxPage;

class ChatInitiationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $account;
    protected $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'Active Number',
            'phone_number_id' => 'PN1',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function service_initiates_conversation_successfully()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
            'status' => 'active',
            'do_not_message' => false,
        ]);

        $service = app(ChatConversationActionService::class);
        $conversation = $service->startConversation($this->user, $contact->id);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals($contact->id, $conversation->contact_id);
        $this->assertEquals('1234567890', $conversation->contact_phone);
        $this->assertEquals('open', $conversation->status);
    }

    /** @test */
    public function service_fails_if_contact_is_blocked()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Blocked User',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
            'status' => 'blocked',
            'do_not_message' => false,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This contact is blocked or has opted out of messaging.');

        $service = app(ChatConversationActionService::class);
        $service->startConversation($this->user, $contact->id);
    }

    /** @test */
    public function api_can_initiate_conversation()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John API',
            'phone' => '9876543210',
            'normalized_phone' => '9876543210',
            'status' => 'active',
            'do_not_message' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/chats', [
                'contact_id' => $contact->id,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.contact_phone', '9876543210');

        $this->assertDatabaseHas('conversations', [
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
        ]);
    }

    /** @test */
    public function api_returns_validation_error_for_out_of_scope_contact()
    {
        $otherCompany = Company::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'primary_email' => 'other@company.com',
            'status' => 'trial',
        ]);
        
        $contact = Contact::create([
            'company_id' => $otherCompany->id,
            'name' => 'Foreign Contact',
            'phone' => '1112223333',
            'normalized_phone' => '1112223333',
            'status' => 'active',
            'do_not_message' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/chats', [
                'contact_id' => $contact->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_id']);
    }

    /** @test */
    public function livewire_inbox_can_initiate_chat()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Livewire Contact',
            'phone' => '5556667777',
            'normalized_phone' => '5556667777',
            'status' => 'active',
            'do_not_message' => false,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ChatInboxPage::class)
            ->assertSet('showInitiateChatModal', false)
            ->call('openInitiateChatModal')
            ->assertSet('showInitiateChatModal', true)
            ->set('contactSearch', 'Livewire')
            ->assertCount('contactsForInitiation', 1)
            ->call('selectAndInitiateChat', $contact->id)
            ->assertSet('showInitiateChatModal', false)
            ->assertSet('selectedConversationId', Conversation::first()->id);
    }
}
