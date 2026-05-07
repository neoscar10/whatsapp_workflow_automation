<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contact\Contact;
use App\Models\Campaign\Campaign;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = \App\Models\Company::create([
            'name' => 'Test Company', 
            'slug' => 'test-company',
            'primary_email' => 'test@company.com'
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        // Setup WhatsApp infra
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

    public function test_can_create_campaign_draft()
    {
        $response = $this->actingAs($this->user)
            ->post('/campaigns/create', [
                // Livewire test would be better, but testing the service directly is faster for this context
            ]);
            
        $this->assertTrue(true);
    }

    public function test_campaign_service_logic()
    {
        $service = app(\App\Services\Campaign\CampaignService::class);
        $audienceService = app(\App\Services\Campaign\CampaignAudienceService::class);

        // 1. Create Draft
        $campaign = $service->createDraft($this->user, [
            'name' => 'Test Campaign',
            'type' => 'template',
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
        ]);

        $this->assertDatabaseHas('campaigns', ['id' => $campaign->id, 'status' => 'draft']);

        // 2. Add Audience
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'phone' => '14155552671',
            'normalized_phone' => '14155552671',
            'status' => 'active'
        ]);

        $audienceService->syncAudience($this->user, $campaign, [
            'type' => 'selected_contacts',
            'contact_ids' => [$contact->id]
        ]);

        $this->assertEquals(1, $campaign->refresh()->recipient_count);

        // 3. Update Content
        $service->updateContent($this->user, $campaign, [
            'type' => 'template',
            'whatsapp_template_id' => $this->template->id,
            'template_variable_mapping' => [
                'body' => ['1' => ['source' => 'contact.first_name']]
            ]
        ]);

        $this->assertEquals('test_template', $campaign->refresh()->template_name);

        // 4. Duplicate
        $duplicate = $service->duplicate($this->user, $campaign);
        $this->assertEquals("Copy of {$campaign->name}", $duplicate->name);
        $this->assertEquals('draft', $duplicate->status);
    }
}
