<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Web\WhatsApp\AccountSetupPage;

class WhatsAppWebhookStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'test-token',
            'waba_id' => '12345',
            'business_id' => '67890',
            'connection_status' => 'connected',
        ]);

        config(['services.whatsapp.app_id' => '111222333']);
    }

    /** @test */
    public function refreshing_webhook_status_updates_local_db_on_success()
    {
        Http::fake([
            'graph.facebook.com/*/12345/subscribed_apps' => Http::response([
                'data' => [
                    [
                        'id' => '111222333',
                        'whatsapp_business_api_data' => [
                            'link' => 'https://example.com'
                        ]
                    ]
                ]
            ], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(AccountSetupPage::class)
            ->call('openWebhookModal')
            ->assertSet('webhookStatus', 'verified')
            ->assertSet('webhookSubscriptionStatus', 'subscribed');

        $this->assertDatabaseHas('whatsapp_accounts', [
            'id' => $this->account->id,
            'webhook_status' => 'verified',
            'webhook_subscription_status' => 'subscribed',
        ]);
        
        $this->assertNotNull($this->account->fresh()->webhook_last_checked_at);
        $this->assertNotNull($this->account->fresh()->webhook_subscribed_at);
    }

    /** @test */
    public function refreshing_webhook_status_handles_not_subscribed()
    {
        Http::fake([
            'graph.facebook.com/*/12345/subscribed_apps' => Http::response([
                'data' => [
                    [
                        'id' => '999999999', // Different app ID
                    ]
                ]
            ], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(AccountSetupPage::class)
            ->call('openWebhookModal')
            ->assertSet('webhookSubscriptionStatus', 'not_subscribed');

        $this->assertDatabaseHas('whatsapp_accounts', [
            'id' => $this->account->id,
            'webhook_status' => 'not_configured', // Or whatever default it had
            'webhook_subscription_status' => 'not_subscribed',
        ]);
    }

    /** @test */
    public function refreshing_webhook_status_captures_errors()
    {
        Http::fake([
            'graph.facebook.com/*/12345/subscribed_apps' => Http::response([
                'error' => [
                    'message' => 'Invalid OAuth access token.',
                    'type' => 'OAuthException',
                    'code' => 190
                ]
            ], 401),
        ]);

        Livewire::actingAs($this->user)
            ->test(AccountSetupPage::class)
            ->call('verifyWebhookHealth')
            ->assertSet('webhookSetupError', 'Failed to check status from Meta: Invalid OAuth access token.');

        $this->assertDatabaseHas('whatsapp_accounts', [
            'id' => $this->account->id,
            'webhook_last_error' => 'Meta API Check Failed: Invalid OAuth access token.',
        ]);
    }
}
