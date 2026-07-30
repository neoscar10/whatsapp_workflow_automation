<?php

namespace App\Livewire\Web\Webhooks;

use App\Models\Webhooks\CompanyWebhook;
use App\Models\Webhooks\CompanyWebhookDelivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use Livewire\Component;
use Livewire\WithPagination;

class CompanyWebhooksPage extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showLogsModal = false;
    public bool $showPingModal = false;

    public ?int $editingWebhookId = null;
    public ?int $viewingLogsWebhookId = null;

    public string $name = '';
    public string $url = '';
    public array $events = ['message.received', 'message.status_update', 'template.status_update'];
    public bool $is_active = true;

    public ?array $pingResult = null;

    public array $availableEvents = [
        'message.received' => 'Inbound Messages (When a contact sends a message)',
        'message.status_update' => 'Message Delivery & Read Status Updates (sent, delivered, read, failed)',
        'template.status_update' => 'WhatsApp Template Status Changes (approved, rejected, paused)',
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $company = Auth::user()?->company;

        $webhooks = $company 
            ? CompanyWebhook::where('company_id', $company->id)->latest()->paginate(10)
            : collect();

        $logs = $this->viewingLogsWebhookId 
            ? CompanyWebhookDelivery::where('company_webhook_id', $this->viewingLogsWebhookId)->latest()->paginate(10)
            : collect();

        return view('livewire.web.webhooks.company-webhooks-page', [
            'webhooks' => $webhooks,
            'logs' => $logs,
        ])->layout('layouts.panel', [
            'title' => 'Outbound Webhooks',
            'activeNav' => 'webhooks.*',
            'topbarTitle' => 'Webhooks Management',
            'topbarBreadcrumbLabel' => 'Developer Webhooks',
        ]);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->url = '';
        $this->events = ['message.received', 'message.status_update', 'template.status_update'];
        $this->is_active = true;
        $this->showCreateModal = true;
    }

    public function saveWebhook(): void
    {
        $this->validate();

        $company = Auth::user()?->company;
        if (!$company) {
            session()->flash('error', 'No associated company profile found.');
            return;
        }

        CompanyWebhook::create([
            'company_id' => $company->id,
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->events,
            'is_active' => $this->is_active,
        ]);

        $this->showCreateModal = false;
        session()->flash('success', 'Webhook URL successfully created!');
    }

    public function editWebhook(int $id): void
    {
        $company = Auth::user()?->company;
        $webhook = CompanyWebhook::where('company_id', $company->id)->findOrFail($id);

        $this->resetValidation();
        $this->editingWebhookId = $webhook->id;
        $this->name = $webhook->name;
        $this->url = $webhook->url;
        $this->events = $webhook->events ?? [];
        $this->is_active = (bool) $webhook->is_active;

        $this->showEditModal = true;
    }

    public function updateWebhook(): void
    {
        $this->validate();

        $company = Auth::user()?->company;
        $webhook = CompanyWebhook::where('company_id', $company->id)->findOrFail($this->editingWebhookId);

        $webhook->update([
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->events,
            'is_active' => $this->is_active,
        ]);

        $this->showEditModal = false;
        session()->flash('success', 'Webhook updated successfully!');
    }

    public function toggleActive(int $id): void
    {
        $company = Auth::user()?->company;
        $webhook = CompanyWebhook::where('company_id', $company->id)->findOrFail($id);
        $webhook->update(['is_active' => !$webhook->is_active]);

        session()->flash('success', 'Webhook status updated.');
    }

    public function deleteWebhook(int $id): void
    {
        $company = Auth::user()?->company;
        $webhook = CompanyWebhook::where('company_id', $company->id)->findOrFail($id);
        $webhook->delete();

        session()->flash('success', 'Webhook deleted successfully.');
    }

    public function sendTestPing(int $id): void
    {
        $company = Auth::user()?->company;
        $webhook = CompanyWebhook::where('company_id', $company->id)->findOrFail($id);

        $testPayload = [
            'event' => 'ping.test',
            'timestamp' => now()->toIso8601String(),
            'message' => 'This is a test webhook payload from WhatsApp Cloud Panel.',
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
            ],
        ];

        $jsonPayload = json_encode($testPayload);
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $webhook->secret);

        $startTime = microtime(true);
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature-256' => $signature,
                'User-Agent' => 'WA-Cloud-Webhook/1.0',
            ])
            ->timeout(10)
            ->withBody($jsonPayload, 'application/json')
            ->post($webhook->url);

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->pingResult = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'duration_ms' => $durationMs,
                'response_body' => mb_substr($response->body(), 0, 1000),
                'error' => $response->successful() ? null : 'Received non-2xx status code',
            ];
        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $this->pingResult = [
                'success' => false,
                'status_code' => null,
                'duration_ms' => $durationMs,
                'response_body' => null,
                'error' => $e->getMessage(),
            ];
        }

        $this->showPingModal = true;
    }

    public function viewLogs(int $id): void
    {
        $this->viewingLogsWebhookId = $id;
        $this->showLogsModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showLogsModal = false;
        $this->showPingModal = false;
        $this->pingResult = null;
    }
}
