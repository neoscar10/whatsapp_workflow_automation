<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\Campaign\CampaignTemplateVariableService;
use App\Services\Template\ChatTemplateDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DynamicUrlTemplateSendTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected WhatsAppAccount $account;
    protected WhatsAppPhoneNumber $phoneNumber;
    protected WhatsAppTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Dynamic Link Test Co',
            'slug' => 'dynamic-link-test-' . uniqid(),
            'primary_email' => 'dynamic@example.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Dynamic Link User',
            'email' => 'dynamicuser@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);

        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'fake_access_token',
            'waba_id' => 'waba_dynamic_123',
            'business_id' => 'biz_dynamic_123',
            'connection_status' => 'connected',
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'Support Number',
            'phone_number_id' => 'phone_id_dynamic_999',
            'phone_number' => '+15559876543',
            'status' => 'active',
        ]);

        $this->template = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'remote_template_name' => 'order_tracking_alert',
            'display_title' => 'Order Tracking Alert',
            'category' => 'utility',
            'language_code' => 'en_US',
            'status' => 'approved',
            'body_text' => 'Hello {{1}}, your order #{{2}} is on the way!',
        ]);

        // Create lowercase 'url' button with dynamic variable {{1}}
        $this->template->buttons()->create([
            'type' => 'url', // Lowercase as stored in DB
            'text' => 'Track Package',
            'url' => 'https://example.com/track/{{1}}',
            'sort_order' => 0,
        ]);
    }

    /** @test */
    public function test_extract_variables_detects_lowercase_url_button_variables()
    {
        $service = app(CampaignTemplateVariableService::class);
        $variables = $service->extractVariables($this->template->fresh(['buttons']));

        $this->assertArrayHasKey('button', $variables);
        $this->assertNotEmpty($variables['button']);
        $this->assertArrayHasKey(0, $variables['button']);
        $this->assertEquals('Track Package', $variables['button'][0][1]['button_text']);
        $this->assertEquals('https://example.com/track/{{1}}', $variables['button'][0][1]['url_preview']);
    }

    /** @test */
    public function test_campaign_build_recipient_payload_includes_url_button_parameters()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Jane Doe',
            'phone' => '+15551112222',
            'normalized_phone' => '15551112222',
        ]);

        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
            'whatsapp_template_id' => $this->template->id,
            'name' => 'Tracking Campaign',
            'type' => 'template',
            'status' => 'draft',
            'template_name' => $this->template->remote_template_name,
            'template_language' => 'en_US',
            'template_variable_mapping' => [
                'body' => [
                    1 => ['source' => 'contact.name'],
                    2 => ['source' => 'static', 'value' => 'ORD-9988'],
                ],
                'button' => [
                    0 => [
                        1 => ['source' => 'static', 'value' => 'TRK-123456'],
                    ]
                ],
            ],
        ]);

        $recipient = CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'company_id' => $this->company->id,
            'contact_id' => $contact->id,
            'phone' => $contact->phone,
            'normalized_phone' => $contact->normalized_phone,
            'name' => $contact->name,
            'status' => 'pending',
        ]);

        $service = app(CampaignTemplateVariableService::class);
        $components = $service->buildRecipientPayload($campaign, $recipient);

        $buttonComponent = null;
        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'button') {
                $buttonComponent = $component;
                break;
            }
        }

        $this->assertNotNull($buttonComponent, 'Button component payload should be present in recipient payload');
        $this->assertEquals('url', $buttonComponent['sub_type']);
        $this->assertEquals('0', $buttonComponent['index']);
        $this->assertCount(1, $buttonComponent['parameters']);
        $this->assertEquals('TRK-123456', $buttonComponent['parameters'][0]['text']);
    }

    /** @test */
    public function test_chat_template_directory_service_extracts_button_variables()
    {
        $directoryService = app(ChatTemplateDirectoryService::class);
        $preview = $directoryService->getTemplatePreview($this->user, $this->template->id);

        $this->assertNotNull($preview);
        $this->assertNotEmpty($preview['variables']);

        $buttonVar = null;
        foreach ($preview['variables'] as $var) {
            if (($var['component'] ?? '') === 'button') {
                $buttonVar = $var;
                break;
            }
        }

        $this->assertNotNull($buttonVar, 'Chat template preview should extract button variable entries');
        $this->assertEquals(0, $buttonVar['button_index']);
        $this->assertEquals(1, $buttonVar['var_index']);
    }
}
