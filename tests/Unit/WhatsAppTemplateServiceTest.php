<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_template_has_pending_status_and_populates_meta_status(): void
    {
        $company = Company::factory()->create();
        $account = WhatsAppAccount::create([
            'company_id' => $company->id,
            'waba_id' => '123456789',
            'phone_number_id' => '987654321',
            'access_token' => 'fake_access_token',
            'connection_status' => 'connected',
        ]);

        $service = app(WhatsAppTemplateService::class);

        $template = $service->createTemplate($account, [
            'remote_template_name' => 'test_pending_template',
            'category' => 'UTILITY',
            'language_code' => 'en_US',
            'header_type' => 'none',
            'body_text' => 'Hello {{1}}, welcome to our platform!',
        ]);

        $this->assertEquals('pending', $template->status);
        $this->assertNotNull($template->meta_status);
        $this->assertEquals('pending', $template->meta_status);
    }

    public function test_sync_templates_with_simulated_account_syncs_local_templates(): void
    {
        $company = Company::factory()->create();
        $account = WhatsAppAccount::create([
            'company_id' => $company->id,
            'waba_id' => '123456789',
            'phone_number_id' => '987654321',
            'access_token' => 'fake_access_token',
            'connection_status' => 'connected',
        ]);

        WhatsAppTemplate::create([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'remote_template_name' => 'simulated_template',
            'category' => 'utility',
            'language_code' => 'en_US',
            'status' => 'pending',
            'meta_status' => 'approved',
            'body_text' => 'Sample body',
        ]);

        $service = app(WhatsAppTemplateService::class);
        $result = $service->syncTemplatesFromMeta($account);

        $this->assertStringContainsString('Successfully synced 1 template(s)', $result['status']);
    }
}
