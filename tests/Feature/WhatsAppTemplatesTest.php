<?php

namespace Tests\Feature;

use App\Livewire\Web\WhatsApp\TemplateCreatePage;
use App\Livewire\Web\WhatsApp\TemplateEditPage;
use App\Livewire\Web\WhatsApp\TemplatesIndexPage;
use App\Livewire\Web\WhatsApp\TemplateShowPage;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected WhatsAppAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company-' . uniqid(),
            'primary_email' => 'test@example.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
        
        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'fake_token',
            'waba_id' => '123456789',
            'business_id' => '987654321',
            'connection_status' => 'connected',
        ]);
        
        Http::preventStrayRequests();
    }

    public function test_index_page_redirects_if_not_connected()
    {
        $this->account->update(['connection_status' => 'disconnected']);
        
        $this->actingAs($this->user)
            ->get(route('whatsapp.templates.index'))
            ->assertRedirect(route('whatsapp.setup.account'));
    }

    public function test_index_page_loads_with_templates()
    {
        WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'hello_world',
            'display_title' => 'Hello World',
            'category' => 'marketing',
            'language_code' => 'en_US',
            'status' => 'approved',
            'body_text' => 'Hello there!',
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplatesIndexPage::class)
            ->assertSee('Hello World')
            ->assertSee('Approved');
    }

    public function test_can_create_template_with_meta_mock()
    {
        // Mock the Meta Create API response
        Http::fake([
            'https://graph.facebook.com/v21.0/123456789/message_templates' => Http::response([
                'id' => '999888777',
                'status' => 'PENDING',
                'category' => 'MARKETING'
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplateCreatePage::class)
            ->set('name', 'test_promo')
            ->set('category', 'marketing')
            ->set('language', 'en_US')
            ->set('headerType', 'text')
            ->set('headerText', 'Big Sale')
            ->set('bodyText', 'Get 50% off today {{1}}')
            ->set('exampleBodyValues.0', 'John')
            ->call('addButton', 'url')
            ->set('buttons.0.text', 'Shop Now')
            ->set('buttons.0.url', 'https://example.com')
            ->call('createTemplate')
            ->assertHasNoErrors()
            ->assertRedirect(route('whatsapp.templates.index'));

        $this->assertDatabaseHas('whatsapp_templates', [
            'company_id' => $this->company->id,
            'remote_template_name' => 'test_promo',
            'remote_template_id' => '999888777',
            'status' => 'pending',
            'body_text' => 'Get 50% off today {{1}}',
        ]);

        $this->assertDatabaseHas('whatsapp_template_buttons', [
            'type' => 'url',
            'text' => 'Shop Now',
            'url' => 'https://example.com',
        ]);
    }

    public function test_create_validation_fails_on_bad_data()
    {
        Livewire::actingAs($this->user)
            ->test(TemplateCreatePage::class)
            // The regex for remote_template_name might be failing if it requires lowercase/underscores only
            ->set('name', 'invalid name!!') 
            ->set('headerType', 'text') 
            ->set('headerText', '') // Should fail required_if
            ->call('createTemplate')
            ->assertHasErrors(['name', 'headerText']);
    }

    public function test_show_page_displays_template_details()
    {
        $template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'alert_msg',
            'display_title' => 'Alert Msg',
            'category' => 'utility',
            'language_code' => 'en_US',
            'status' => 'approved',
            'body_text' => 'System alert!',
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplateShowPage::class, ['id' => $template->id])
            ->assertSee('Alert Msg')
            ->assertSee('utility')
            ->assertSee('System alert!');
    }

    public function test_can_edit_draft_template_with_meta_mock()
    {
        $template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'draft_promo',
            'remote_template_id' => '555444333',
            'display_title' => 'Draft Promo',
            'category' => 'marketing',
            'language_code' => 'en_US',
            'status' => 'rejected',
            'body_text' => 'Old text',
        ]);

        // Mock the Meta Update API response
        Http::fake([
            'https://graph.facebook.com/v21.0/555444333' => Http::response([
                'id' => '555444333',
                'status' => 'PENDING',
                'category' => 'MARKETING'
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplateEditPage::class, ['id' => $template->id])
            ->assertSet('name', 'draft_promo') // Pre-filled
            ->set('bodyText', 'New updated text')
            ->call('updateTemplate')
            ->assertHasNoErrors()
            ->assertRedirect(route('whatsapp.templates.show', $template->id));

        $this->assertDatabaseHas('whatsapp_templates', [
            'id' => $template->id,
            'status' => 'pending', // Reverts to pending on edit
            'body_text' => 'New updated text',
        ]);
    }

    public function test_can_delete_template()
    {
        $template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'to_be_deleted',
            'category' => 'marketing',
            'language_code' => 'en_US',
            'status' => 'draft',
            'body_text' => 'Delete me',
        ]);

        // Mock Delete API
        Http::fake([
            'https://graph.facebook.com/v21.0/123456789/message_templates*' => Http::response([
                'success' => true
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplatesIndexPage::class)
            ->call('confirmDelete', $template->id)
            ->call('deleteTemplate')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('whatsapp_templates', [
            'id' => $template->id
        ]);
    }

    public function test_can_sync_templates_from_meta()
    {
        // Mock Sync API with proper Meta graph payload structure
        Http::fake([
            'https://graph.facebook.com/v21.0/123456789/message_templates*' => Http::response([
                'data' => [
                    [
                        'id' => 'abc123def',
                        'name' => 'synced_remote_temp',
                        'language' => 'en_US',
                        'status' => 'APPROVED',
                        'category' => 'MARKETING',
                        'components' => [
                            ['type' => 'BODY', 'text' => 'Remote body text']
                        ]
                    ]
                ],
                'paging' => [
                    'cursors' => [
                        'before' => 'xxx',
                        'after' => null
                    ]
                ]
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(TemplatesIndexPage::class)
            ->call('syncTemplates')
            ->assertSet('syncError', null)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_templates', [
            'company_id' => $this->company->id,
            'remote_template_name' => 'synced_remote_temp',
            'status' => 'approved',
            'body_text' => 'Remote body text',
        ]);
    }
}
