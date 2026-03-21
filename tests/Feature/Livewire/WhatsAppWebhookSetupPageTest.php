<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Web\WhatsApp\AccountSetupPage;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsAppAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppWebhookSetupPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_account_setup_page_and_webhook_status()
    {
        $company = Company::create(['name' => 'Test', 'slug' => 'test']);
        $user = User::factory()->create(['company_id' => $company->id]);

        WhatsAppAccount::create([
            'company_id' => $company->id,
            'access_token' => 'token123',
            'waba_id' => 'waba123',
            'webhook_status' => 'verified',
            'webhook_subscription_status' => 'not_subscribed',
        ]);

        Livewire::actingAs($user)
            ->test(AccountSetupPage::class)
            ->assertSet('webhookStatus', 'verified')
            ->assertSet('webhookSubscriptionStatus', 'not_subscribed')
            ->assertSee('Setup Webhooks');
    }

    public function test_can_open_modal_and_subscribe_app()
    {
        $company = Company::create(['name' => 'Test', 'slug' => 'test2']);
        $user = User::factory()->create(['company_id' => $company->id]);

        $account = WhatsAppAccount::create([
            'company_id' => $company->id,
            'access_token' => 'token123',
            'waba_id' => 'waba123',
        ]);

        Http::fake([
            'graph.facebook.com/*/subscribed_apps' => Http::response(['success' => true], 200),
        ]);

        Livewire::actingAs($user)
            ->test(AccountSetupPage::class)
            ->call('openWebhookModal')
            ->assertSet('showWebhookModal', true)
            ->call('subscribeWebhook')
            ->assertSet('webhookSubscriptionStatus', 'subscribed')
            ->assertSet('webhookSetupMessage', 'Successfully subscribed app to WhatsApp Business Account.');

        $this->assertEquals('subscribed', $account->fresh()->webhook_subscription_status);
    }
}
