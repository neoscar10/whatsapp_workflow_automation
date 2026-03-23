<?php

namespace Tests\Feature\Web;

use App\Livewire\Web\Chats\ChatInboxPage;
use App\Models\Chat\Conversation;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatInboxPageTest extends TestCase
{
    use RefreshDatabase;

    private function setupUserAndCompany()
    {
        $company = Company::forceCreate(['name' => 'Test Company', 'slug' => 'test-company-' . uniqid(), 'primary_email' => uniqid() . 'test@example.com']);
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test' . rand(1, 1000) . '@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        return [$user, $company];
    }

    private function setupWhatsAppAccount($company)
    {
        return \App\Models\WhatsApp\WhatsAppAccount::forceCreate([
            'company_id' => $company->id,
            'access_token' => 'dummy-token',
            'waba_id' => '12345',
            'business_id' => '67890',
            'connection_status' => 'connected',
        ]);
    }

    public function test_guest_cannot_access_chats_page()
    {
        $this->get(route('chats.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_chats_page()
    {
        [$user, $company] = $this->setupUserAndCompany();
        
        $this->actingAs($user)
            ->get(route('chats.index'))
            ->assertSuccessful();
    }

    public function test_chats_page_only_shows_company_scoped_conversations()
    {
        [$user1, $company1] = $this->setupUserAndCompany();
        [$user2, $company2] = $this->setupUserAndCompany();
        
        // C1 belongs to User 1
        $c1 = Conversation::create(['company_id' => $company1->id, 'contact_name' => 'JohnScopingTest', 'contact_phone' => '123']);
        // C2 belongs to User 2
        $c2 = Conversation::create(['company_id' => $company2->id, 'contact_name' => 'JaneScopingTest', 'contact_phone' => '456']);

        Livewire::actingAs($user1)
            ->test(ChatInboxPage::class)
            ->assertSee('JohnScopingTest')
            ->assertDontSee('JaneScopingTest');
    }

    public function test_send_message_feature()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->set('messageText', 'Hello John')
            ->call('sendMessage')
            ->assertSet('messageText', '');

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'body' => 'Hello John',
            'sent_by_user_id' => $user->id,
        ]);
    }

    public function test_save_note_feature()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->set('noteText', 'Important note')
            ->call('saveNote')
            ->assertSet('noteText', '')
            ->assertSee('Note saved successfully');

        $this->assertDatabaseHas('conversation_notes', [
            'conversation_id' => $conversation->id,
            'note' => 'Important note',
            'user_id' => $user->id,
        ]);
    }

    public function test_close_chat_updates_status()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123', 'status' => 'open']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('closeChat')
            ->assertSee('Chat closed securely');

        $this->assertEquals('closed', $conversation->fresh()->status);
    }

    public function test_renders_empty_state_when_no_conversations_exist()
    {
        [$user, $company] = $this->setupUserAndCompany();

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->assertSee('Select a conversation to start messaging')
            ->assertSee('No conversations match your criteria.');
    }

    public function test_renders_empty_state_when_no_conversation_selected()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'JohnState', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            // It sees the conversation in the left list
            ->assertSee('JohnState')
            // It also sees the empty state because none is selected actively
            ->assertSee('Select a conversation to start messaging');
    }

    public function test_selecting_conversation_removes_empty_state()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'JohnTransition', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->assertSee('Select a conversation to start messaging')
            ->call('selectConversation', $conversation->id)
            ->assertSet('selectedConversationId', $conversation->id)
            ->assertDontSee('Select a conversation to start messaging')
            ->assertSee('JohnTransition');
    }

    public function test_search_filters_list_while_empty_state_visible()
    {
        [$user, $company] = $this->setupUserAndCompany();
        Conversation::create(['company_id' => $company->id, 'contact_name' => 'JohnFilter', 'contact_phone' => '123']);
        Conversation::create(['company_id' => $company->id, 'contact_name' => 'JaneFilter', 'contact_phone' => '456']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->assertSee('JohnFilter')
            ->assertSee('JaneFilter')
            ->assertSee('Select a conversation to start messaging') // Empty state still visible
            ->set('search', 'Jane') // Filtering
            ->assertSee('JaneFilter')
            ->assertDontSee('JohnFilter') // John should be filtered out
            ->assertSee('Select a conversation to start messaging'); // Empty state STILL visible
    }

    public function test_can_open_assign_modal_and_load_agents()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $agent2 = User::forceCreate(['name' => 'Agent Two', 'email' => uniqid().'@test.com', 'password' => bcrypt('password'), 'company_id' => $company->id]);
        
        $companyOther = Company::forceCreate(['name' => 'Other', 'slug' => 'other-'.uniqid(), 'primary_email' => uniqid().'@test.com']);
        $agentOther = User::forceCreate(['name' => 'Agent Other', 'email' => uniqid().'@test.com', 'password' => bcrypt('password'), 'company_id' => $companyOther->id]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openAssignAgentModal')
            ->assertSet('showAssignAgentModal', true)
            ->assertSee('Agent Two')
            ->assertDontSee('Agent Other');
    }

    public function test_assign_agent_search_filters_results()
    {
        [$user, $company] = $this->setupUserAndCompany();
        User::forceCreate(['name' => 'Agent Alpha', 'email' => uniqid().'@test.com', 'password' => bcrypt('password'), 'company_id' => $company->id]);
        User::forceCreate(['name' => 'Agent Beta', 'email' => uniqid().'@test.com', 'password' => bcrypt('password'), 'company_id' => $company->id]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openAssignAgentModal')
            ->assertSee('Agent Alpha')
            ->assertSee('Agent Beta')
            ->set('agentSearch', 'Alpha')
            ->assertSee('Agent Alpha')
            ->assertDontSee('Agent Beta');
    }

    public function test_assignment_successfully_updates_conversation()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $agent = User::forceCreate(['name' => 'Agent Assignee', 'email' => uniqid().'@test.com', 'password' => bcrypt('password'), 'company_id' => $company->id]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123', 'assignment_status' => 'unassigned']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openAssignAgentModal')
            ->set('selectedAgentId', $agent->id)
            ->call('assignChat')
            ->assertSet('showAssignAgentModal', false)
            ->assertSee('Chat assigned securely');
            
        $conversation->refresh();
        $this->assertEquals($agent->id, $conversation->assigned_user_id);
        $this->assertEquals('assigned', $conversation->assignment_status);
        $this->assertNotNull($conversation->assigned_at);
    }

    public function test_can_open_template_modal_and_load_templates()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $account = $this->setupWhatsAppAccount($company);
        WhatsAppTemplate::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'remote_template_name' => 'hello_world',
            'display_title' => 'Hello World',
            'category' => 'marketing',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Hello {{1}}',
        ]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openTemplateSendModal')
            ->assertSet('showTemplateModal', true)
            ->assertSee('Hello World')
            ->assertSee('Marketing');
    }

    public function test_template_search_filters_results()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $account = $this->setupWhatsAppAccount($company);
        
        WhatsAppTemplate::forceCreate(['company_id' => $company->id, 'whatsapp_account_id' => $account->id, 'remote_template_name' => 'apple_template', 'display_title' => 'Apple', 'category' => 'marketing', 'language_code' => 'en', 'status' => 'approved', 'body_text' => 'A']);
        WhatsAppTemplate::forceCreate(['company_id' => $company->id, 'whatsapp_account_id' => $account->id, 'remote_template_name' => 'banana_template', 'display_title' => 'Banana', 'category' => 'marketing', 'language_code' => 'en', 'status' => 'approved', 'body_text' => 'B']);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openTemplateSendModal')
            ->assertCount('availableTemplates', 2)
            ->set('templateSearch', 'Apple')
            ->call('loadTemplates')
            ->assertCount('availableTemplates', 1)
            ->tap(fn($lw) => $this->assertEquals('Apple', $lw->availableTemplates[0]['name']));
    }

    public function test_selecting_template_updates_preview()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $account = $this->setupWhatsAppAccount($company);
        $template = WhatsAppTemplate::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'remote_template_name' => 'hello_preview',
            'display_title' => 'Hello Preview',
            'category' => 'marketing',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Preview Body'
        ]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openTemplateSendModal')
            ->call('selectTemplate', $template->id)
            ->assertSee('Preview Body')
            ->assertSee('Live Preview');
    }

    public function test_sending_template_updates_conversation()
    {
        [$user, $company] = $this->setupUserAndCompany();
        $account = $this->setupWhatsAppAccount($company);
        $template = WhatsAppTemplate::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'remote_template_name' => 'send_me',
            'display_title' => 'Send Me',
            'category' => 'utility',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Real Template Body'
        ]);
        
        $conversation = Conversation::create(['company_id' => $company->id, 'contact_name' => 'John', 'contact_phone' => '123']);

        Livewire::actingAs($user)
            ->test(ChatInboxPage::class)
            ->set('selectedConversationId', $conversation->id)
            ->call('openTemplateSendModal')
            ->call('selectTemplate', $template->id)
            ->call('sendSelectedTemplate')
            ->assertSet('showTemplateModal', false)
            ->assertSee('Template sent successfully');
            
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'message_type' => 'template',
            'body' => 'Real Template Body',
        ]);
        
        $this->assertStringContainsString('Template: Send Me', $conversation->fresh()->last_message_preview);
    }
}
