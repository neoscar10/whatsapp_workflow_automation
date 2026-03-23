<?php

namespace App\Livewire\Web\WhatsApp;

use App\Services\WhatsApp\WhatsAppAccountSetupService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AccountSetupPage extends Component
{
    public $access_token = '';
    public $waba_id = '';
    public $business_id = '';
    public $showAccessToken = false;
    public $hasSavedToken = false;
    public $isConnected = false;
    public $connectionStatus = 'not_connected';
    public $webhookStatus = 'not_configured';
    public $webhookSubscriptionStatus = 'not_subscribed';
    public $webhookCallbackUrl = '';
    public $webhookVerifyToken = '';
    public $webhookVerifiedAt = null;
    public $webhookLastCheckedAt = null;
    public $webhookLastError = null;
    public $webhookSetupMessage = null;
    public $webhookSetupError = null;
    public $showWebhookModal = false;
    public $has_connected_account = false;
    public $lastSyncedAt = null;
    public $lastSyncError = null;

    public function mount(WhatsAppAccountSetupService $service, \App\Services\WhatsApp\WhatsAppWebhookSetupService $webhookService)
    {
        $this->hydrateFromData($service->getSetupDataForUser(Auth::user()));
        $this->refreshWebhookData($webhookService);
    }

    public function hydrateFromData(array $data)
    {
        $this->isConnected = $data['is_connected'];
        $this->connectionStatus = $data['connection_status'];
        $this->hasSavedToken = $data['has_saved_token'];
        $this->waba_id = $data['waba_id'];
        $this->business_id = $data['business_id'];
        $this->webhookStatus = $data['webhook_status'];
        $this->lastSyncedAt = $data['last_synced_at'] ?? null;
        $this->lastSyncError = $data['last_sync_error'] ?? null;
        $this->access_token = ''; // Never prefill stored token
    }

    public function toggleAccessTokenVisibility()
    {
        $this->showAccessToken = !$this->showAccessToken;
    }

    public function save(WhatsAppAccountSetupService $service)
    {
        $rules = [
            'waba_id' => 'required|string|max:50|regex:/^[0-9]+$/',
            'business_id' => 'required|string|max:50|regex:/^[0-9]+$/',
        ];

        if (!$this->hasSavedToken) {
            $rules['access_token'] = 'required|string|max:4096';
        } else {
            $rules['access_token'] = 'nullable|string|max:4096';
        }

        $this->validate($rules);

        $data = [
            'access_token' => $this->access_token,
            'waba_id' => $this->waba_id,
            'business_id' => $this->business_id,
        ];

        $newData = $service->saveSetupForUser(Auth::user(), $data);
        $this->hydrateFromData($newData);

        session()->flash('success', 'WhatsApp account credentials saved successfully.');
    }

    public function discardChanges(WhatsAppAccountSetupService $service)
    {
        $this->hydrateFromData($service->resetDataForUser(Auth::user()));
        $this->resetErrorBag();
    }
    public function render()
    {
        return view('livewire.web.whatsapp.account-setup-page')
            ->layout('layouts.panel', [
                'title' => 'WhatsApp Account Setup - Cloud Panel',
                'activeNav' => 'whatsapp-setup',
            ]);
    }

    // --- Webhook Modal Methods ---

    public function openWebhookModal(\App\Services\WhatsApp\WhatsAppWebhookSetupService $service)
    {
        $this->refreshWebhookData($service);
        $this->showWebhookModal = true;
    }

    public function closeWebhookModal()
    {
        $this->showWebhookModal = false;
        $this->webhookSetupError = null;
        $this->webhookSetupMessage = null;
    }

    public function subscribeWebhook(\App\Services\WhatsApp\WhatsAppWebhookSetupService $service)
    {
        $this->webhookSetupError = null;
        $this->webhookSetupMessage = null;

        $result = $service->subscribeAppToWabaForUser(\Illuminate\Support\Facades\Auth::user());

        if ($result['success']) {
            $this->webhookSetupMessage = $result['message'];
        } else {
            $this->webhookSetupError = $result['message'];
        }

        $this->refreshWebhookData($service);
    }

    public function verifyWebhookHealth(\App\Services\WhatsApp\WhatsAppWebhookSetupService $service)
    {
        $this->webhookSetupError = null;
        $this->webhookSetupMessage = null;

        $result = $service->refreshWebhookHealthForUser(\Illuminate\Support\Facades\Auth::user());

        if ($result['success']) {
            $this->webhookSetupMessage = $result['message'];
        } else {
            $this->webhookSetupError = $result['message'] ?? 'Check failed.';
        }

        $this->refreshWebhookData($service);
    }

    public function refreshWebhookData(\App\Services\WhatsApp\WhatsAppWebhookSetupService $service)
    {
        $data = $service->getSetupDataForUser(\Illuminate\Support\Facades\Auth::user());
        
        $this->webhookStatus = $data['webhook_status'];
        $this->webhookSubscriptionStatus = $data['webhook_subscription_status'];
        $this->has_connected_account = $data['has_connected_account'];
        $this->webhookCallbackUrl = $data['callback_url'];
        $this->webhookVerifyToken = $data['verify_token'];
        $this->webhookVerifiedAt = $data['webhook_verified_at'];
        $this->webhookLastCheckedAt = $data['webhook_last_checked_at'];
        $this->webhookLastError = $data['webhook_last_error'];
    }
}
