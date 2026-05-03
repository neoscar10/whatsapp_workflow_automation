<?php

namespace Tests\Feature\Api\V1\WhatsApp;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplatesApiTest extends TestCase
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
            'slug' => 'test-company',
            'status' => 'active',
            'primary_email' => 'test@company.com',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);

        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'name' => 'Test Account',
            'whatsapp_business_account_id' => '12345',
            'access_token' => 'fake-token',
            'connection_status' => 'connected',
        ]);
    }

    public function test_unauthenticated_user_cannot_list_templates()
    {
        $response = $this->getJson(route('api.v1.whatsapp.templates.index'));
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_templates()
    {
        WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'template_1',
            'display_title' => 'Template 1',
            'category' => 'marketing',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Hello {{1}}',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.whatsapp.templates.index'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'template_1');
    }

    public function test_user_cannot_see_templates_from_another_company()
    {
        $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active', 'primary_email' => 'other@company.com']);
        $otherAccount = WhatsAppAccount::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Account',
            'whatsapp_business_account_id' => '67890',
            'access_token' => 'other-token',
        ]);
        
        $otherTemplate = WhatsAppTemplate::create([
            'company_id' => $otherCompany->id,
            'whatsapp_account_id' => $otherAccount->id,
            'remote_template_name' => 'other_template',
            'display_title' => 'Other Template',
            'category' => 'marketing',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Secret',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.whatsapp.templates.index'));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.whatsapp.templates.show', ['id' => $otherTemplate->id]));

        $response->assertStatus(404);
    }

    public function test_user_can_show_template_details()
    {
        $template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'template_detail',
            'display_title' => 'Template Detail',
            'category' => 'utility',
            'language_code' => 'en_US',
            'status' => 'approved',
            'body_text' => 'Detail view',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.whatsapp.templates.show', ['id' => $template->id]));

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'template_detail')
            ->assertJsonPath('data.category', 'utility');
    }

    public function test_user_can_delete_template()
    {
        $template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'to_delete',
            'display_title' => 'To Delete',
            'category' => 'marketing',
            'language_code' => 'en',
            'status' => 'approved',
            'body_text' => 'Delete me',
        ]);

        // Mock the service to avoid calling Meta API
        $this->mock(\App\Services\WhatsApp\WhatsAppTemplateService::class, function ($mock) use ($template) {
            $mock->shouldReceive('findTemplateForCompany')->andReturn($template);
            $mock->shouldReceive('deleteTemplate')->once()->with($template);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson(route('api.v1.whatsapp.templates.destroy', ['id' => $template->id]));

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Template deleted successfully.');
    }
}
