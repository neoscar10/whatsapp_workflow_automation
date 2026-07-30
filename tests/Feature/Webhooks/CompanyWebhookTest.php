<?php

namespace Tests\Feature\Webhooks;

use App\Jobs\Webhooks\DispatchCompanyWebhookJob;
use App\Livewire\Web\Webhooks\CompanyWebhooksPage;
use App\Models\Company;
use App\Models\User;
use App\Models\Webhooks\CompanyWebhook;
use App\Models\Webhooks\CompanyWebhookDelivery;
use App\Services\Webhooks\CompanyWebhookDispatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create([
            'name' => 'Test Company Webhooks Ltd',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);
    }

    public function test_user_can_access_webhooks_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('webhooks.index'));
        $response->assertStatus(200);
        $response->assertSee('Outbound Webhooks');
    }

    public function test_user_can_create_a_new_webhook_endpoint(): void
    {
        Livewire::actingAs($this->user)
            ->test(CompanyWebhooksPage::class)
            ->call('openCreateModal')
            ->set('name', 'Zapier Automation')
            ->set('url', 'https://hooks.zapier.com/hooks/catch/12345/abcde')
            ->set('events', ['message.received', 'message.status_update'])
            ->set('is_active', true)
            ->call('saveWebhook')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_webhooks', [
            'company_id' => $this->company->id,
            'name' => 'Zapier Automation',
            'url' => 'https://hooks.zapier.com/hooks/catch/12345/abcde',
            'is_active' => true,
        ]);
    }

    public function test_dispatcher_service_queues_job_for_subscribed_events(): void
    {
        Queue::fake();

        $webhook = CompanyWebhook::create([
            'company_id' => $this->company->id,
            'name' => 'CRM Forwarder',
            'url' => 'https://crm.example.com/webhook',
            'events' => ['message.received'],
            'is_active' => true,
        ]);

        $dispatcher = app(CompanyWebhookDispatcherService::class);

        // Matching event
        $dispatcher->dispatch($this->company, 'message.received', ['message_id' => 123]);
        Queue::assertPushed(DispatchCompanyWebhookJob::class, function ($job) use ($webhook) {
            return $job->companyWebhookId === $webhook->id && $job->eventType === 'message.received';
        });

        // Unsubscribed event
        Queue::fake();
        $dispatcher->dispatch($this->company, 'template.status_update', ['template_id' => 456]);
        Queue::assertNotPushed(DispatchCompanyWebhookJob::class);
    }

    public function test_job_sends_http_post_with_hmac_signature_and_logs_delivery(): void
    {
        Http::fake([
            'https://crm.example.com/webhook' => Http::response(['status' => 'received'], 200),
        ]);

        $webhook = CompanyWebhook::create([
            'company_id' => $this->company->id,
            'name' => 'CRM Forwarder',
            'url' => 'https://crm.example.com/webhook',
            'secret' => 'whsec_test_secret_key_12345',
            'events' => ['message.received'],
            'is_active' => true,
        ]);

        $job = new DispatchCompanyWebhookJob(
            companyWebhookId: $webhook->id,
            eventType: 'message.received',
            payload: ['text' => 'Hello World']
        );

        $job->handle();

        Http::assertSent(function ($request) use ($webhook) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->body(), $webhook->secret);
            return $request->url() === 'https://crm.example.com/webhook' &&
                   $request->header('X-Webhook-Signature-256')[0] === $expectedSignature;
        });

        $this->assertDatabaseHas('company_webhook_deliveries', [
            'company_webhook_id' => $webhook->id,
            'event_type' => 'message.received',
            'status_code' => 200,
        ]);
    }

    public function test_user_can_send_test_ping_from_ui(): void
    {
        Http::fake([
            'https://webhook.site/test-ping' => Http::response(['ping' => 'pong'], 200),
        ]);

        $webhook = CompanyWebhook::create([
            'company_id' => $this->company->id,
            'name' => 'Webhook Site Test',
            'url' => 'https://webhook.site/test-ping',
            'events' => ['message.received'],
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(CompanyWebhooksPage::class)
            ->call('sendTestPing', $webhook->id)
            ->assertSet('pingResult.success', true)
            ->assertSet('pingResult.status_code', 200);
    }
}
