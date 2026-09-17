<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppApiBugFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected WhatsAppAccount $account;
    protected WhatsAppPhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'BugFix Test Company',
            'slug' => 'bugfix-test-company-' . uniqid(),
            'primary_email' => 'bugfix@example.com',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'BugFix User',
            'email' => 'bugfixuser@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);

        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'fake_access_token',
            'waba_id' => 'fake_waba_id',
            'business_id' => 'fake_business_id',
            'connection_status' => 'connected',
            'webhook_status' => 'not_configured',
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'display_name' => 'Original Number Name',
            'phone_number_id' => 'phone_12345',
            'phone_number' => '+15551234567',
            'status' => 'active',
        ]);

        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['id' => '999888', 'status' => 'PENDING'], 200),
        ]);
    }

    /** @test */
    public function bug1_patch_whatsapp_setup_account_updates_webhook_callback_url_without_302()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/v1/whatsapp/setup/account', [
                'webhook_callback_url' => 'https://example.com/webhook',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.webhook_status', 'configured');

        $this->assertDatabaseHas('whatsapp_accounts', [
            'id' => $this->account->id,
            'webhook_callback_url' => 'https://example.com/webhook',
            'webhook_status' => 'configured',
        ]);
    }

    /** @test */
    public function bug2_patch_phone_number_updates_display_name_without_302()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/whatsapp/setup/phone-numbers/{$this->phoneNumber->id}", [
                'display_name' => 'My Business Number',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.display_name', 'My Business Number');

        $this->assertDatabaseHas('whatsapp_phone_numbers', [
            'id' => $this->phoneNumber->id,
            'display_name' => 'My Business Number',
            'phone_number_id' => 'phone_12345',
        ]);
    }

    /** @test */
    public function bug3_post_whatsapp_templates_accepts_button_url_with_dynamic_variables()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/whatsapp/templates', [
                'name' => 'my_dynamic_template',
                'category' => 'marketing',
                'language_code' => 'en',
                'header_type' => 'none',
                'body_text' => 'Hello {{1}}, click here to track your order:',
                'buttons' => [
                    [
                        'type' => 'url',
                        'text' => 'Track Order',
                        'url' => 'https://example.com/track/{{1}}',
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'my_dynamic_template');

        $this->assertDatabaseHas('whatsapp_templates', [
            'company_id' => $this->company->id,
            'remote_template_name' => 'my_dynamic_template',
        ]);
    }

    /** @test */
    public function bug4_post_webhooks_returns_422_json_instead_of_302_redirect_on_invalid_event()
    {
        // Notice we do NOT set Accept: application/json explicitly here to test header tolerance
        $response = $this->actingAs($this->user, 'sanctum')
            ->call('POST', '/api/v1/webhooks', [
                'name' => 'My Webhook',
                'url' => 'https://example.com/webhook',
                'events' => ['message.inbound', 'message.sent'],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    /** @test */
    public function api_validation_errors_always_return_422_json_even_without_accept_header()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->call('PATCH', '/api/v1/whatsapp/setup/account', [
                'webhook_callback_url' => 'not-a-valid-url',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['webhook_callback_url']]);
    }
}
