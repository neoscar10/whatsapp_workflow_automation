<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\Campaign\Campaign;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class CampaignApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $phoneNumber;
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Test Company', 'slug' => 'test-company', 'primary_email' => 'test@company.com']);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        $account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'name' => 'Test Account',
            'waba_id' => '12345',
            'access_token' => 'fake-token'
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => '67890',
            'phone_number' => '1234567890',
            'display_name' => 'Test Phone',
            'status' => 'active'
        ]);

        $this->template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $account->id,
            'remote_template_name' => 'test_template',
            'language_code' => 'en_US',
            'status' => 'approved',
            'category' => 'utility',
            'body_text' => 'Hello {{1}}!'
        ]);
    }

    public function test_can_list_campaigns()
    {
        Campaign::create([
            'company_id' => $this->company->id,
            'name' => 'Campaign 1',
            'type' => 'template',
            'status' => 'draft',
            'created_by' => $this->user->id,
            'slug' => 'campaign-1'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_cannot_see_other_company_campaigns()
    {
        $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'primary_email' => 'other@test.com']);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
        
        Campaign::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Campaign',
            'type' => 'template',
            'status' => 'draft',
            'created_by' => $otherUser->id,
            'slug' => 'other-campaign'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');
    }

    public function test_can_create_campaign()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/campaigns', [
            'name' => 'New API Campaign',
            'type' => 'template',
            'whatsapp_phone_number_id' => $this->phoneNumber->id
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New API Campaign');
            
        $this->assertDatabaseHas('campaigns', ['name' => 'New API Campaign', 'company_id' => $this->company->id]);
    }

    public function test_can_sync_audience()
    {
        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'name' => 'Audience Test',
            'type' => 'template',
            'status' => 'draft',
            'created_by' => $this->user->id,
            'slug' => 'audience-test'
        ]);

        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'phone' => '14155552671',
            'normalized_phone' => '14155552671',
            'status' => 'active'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/campaigns/{$campaign->id}/audience", [
            'audience_type' => 'selected_contacts',
            'contact_ids' => [$contact->id]
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $campaign->refresh()->recipient_count);
    }

    public function test_can_update_content()
    {
        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'name' => 'Content Update Test',
            'type' => 'template',
            'status' => 'draft',
            'created_by' => $this->user->id,
            'slug' => 'content-update-test'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/campaigns/{$campaign->id}/content", [
            'type' => 'template',
            'whatsapp_template_id' => $this->template->id,
            'template_variable_mapping' => [
                'body' => ['1' => ['source' => 'contact.first_name']]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertEquals($this->template->id, $campaign->refresh()->whatsapp_template_id);
    }

    public function test_can_send_campaign()
    {
        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'name' => 'Send Test',
            'type' => 'template',
            'status' => 'draft',
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
            'whatsapp_template_id' => $this->template->id,
            'created_by' => $this->user->id,
            'slug' => 'send-test'
        ]);

        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Recipient 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
            'status' => 'active'
        ]);

        $campaign->recipients()->create([
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
            'status' => 'pending'
        ]);
        $campaign->update(['recipient_count' => 1]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/campaigns/{$campaign->id}/send");

        $response->assertStatus(200);
        $this->assertNotEquals('draft', $campaign->refresh()->status);
    }
}
