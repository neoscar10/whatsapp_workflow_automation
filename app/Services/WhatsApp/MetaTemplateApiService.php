<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaTemplateApiService
{
    protected string $baseUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiVersion = config('services.whatsapp.version', 'v21.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    /**
     * Determines whether an account is using simulated/fake credentials.
     */
    protected function isSimulatedAccount(WhatsAppAccount $account): bool
    {
        // If a test explicitly registered HTTP fakes with Http::fake(), allow it to hit the stub callbacks
        try {
            if (count(Http::stubCallbacks()) > 0) {
                return false;
            }
        } catch (\Throwable $e) {
            // Fallback if stubCallbacks isn't available
        }

        if (empty($account->access_token) || str_starts_with($account->access_token, 'fake_') || $account->access_token === 'fake_access_token' || $account->access_token === 'simulated_token') {
            return true;
        }

        if (config('services.whatsapp.simulator.enabled') && ($account->waba_id === 'fake_waba_id' || empty($account->waba_id))) {
            return true;
        }

        return false;
    }

    /**
     * Lists templates for a specific WABA ID.
     */
    public function listTemplates(WhatsAppAccount $account, array $params = []): array
    {
        if ($this->isSimulatedAccount($account)) {
            // In simulated mode, mirror existing local templates or return mock items
            $localTemplates = \App\Models\WhatsApp\WhatsAppTemplate::where('whatsapp_account_id', $account->id)->get();

            if ($localTemplates->count() > 0) {
                $mockData = $localTemplates->map(function ($tpl) {
                    return [
                        'id' => $tpl->remote_template_id ?: ('mock_' . $tpl->id),
                        'name' => $tpl->remote_template_name,
                        'language' => $tpl->language_code,
                        'status' => strtoupper($tpl->meta_status ?: $tpl->status ?: 'APPROVED'),
                        'category' => strtoupper($tpl->category),
                        'components' => [
                            ['type' => 'HEADER', 'format' => strtoupper($tpl->header_type ?: 'NONE'), 'text' => $tpl->header_text],
                            ['type' => 'BODY', 'text' => $tpl->body_text],
                            ['type' => 'FOOTER', 'text' => $tpl->footer_text],
                        ],
                    ];
                })->toArray();

                return ['data' => $mockData];
            }

            return [
                'data' => []
            ];
        }

        $this->ensureAccountConnected($account);

        $defaultParams = [
            'limit' => 50,
        ];

        $response = Http::withToken($account->access_token)
            ->get("{$this->baseUrl}/{$account->waba_id}/message_templates", array_merge($defaultParams, $params));

        if ($response->failed()) {
            $this->logError('listTemplates', $account, $response);
            throw new \Exception('Failed to list templates from Meta: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Fetches all templates, handling pagination if necessary.
     */
    public function fetchAllTemplates(WhatsAppAccount $account): array
    {
        $allTemplates = [];
        $nextPage = null;

        do {
            $params = $nextPage ? ['after' => $nextPage] : [];
            $response = $this->listTemplates($account, $params);
            
            if (isset($response['data'])) {
                $allTemplates = array_merge($allTemplates, $response['data']);
            }

            $nextPage = $response['paging']['cursors']['after'] ?? null;
        } while ($nextPage);

        return $allTemplates;
    }

    /**
     * Creates a single template on the WABA.
     */
    public function createTemplate(WhatsAppAccount $account, array $payload): array
    {
        if ($this->isSimulatedAccount($account)) {
            return [
                'id' => 'mock_waba_template_' . rand(100000, 999999),
                'status' => 'PENDING',
            ];
        }

        $this->ensureAccountConnected($account);

        $response = Http::withToken($account->access_token)
            ->post("{$this->baseUrl}/{$account->waba_id}/message_templates", $payload);

        if ($response->failed()) {
            $this->logError('createTemplate', $account, $response, $payload);
            throw new \Exception('Failed to create template on Meta: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Updates an existing template directly, if supported by its state.
     * Meta requires replacing all components on update.
     */
    public function updateTemplate(WhatsAppAccount $account, string $remoteTemplateId, array $payload): array
    {
        if ($this->isSimulatedAccount($account)) {
            return [
                'id' => $remoteTemplateId,
                'status' => 'PENDING',
            ];
        }

        $this->ensureAccountConnected($account);

        $response = Http::withToken($account->access_token)
            ->post("{$this->baseUrl}/{$remoteTemplateId}", $payload);

        if ($response->failed()) {
            $this->logError('updateTemplate', $account, $response, $payload);
            throw new \Exception('Failed to update template on Meta: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Deletes a template from the WABA.
     */
    public function deleteTemplate(WhatsAppAccount $account, string $name): array
    {
        if ($this->isSimulatedAccount($account)) {
            return [
                'success' => true,
            ];
        }

        $this->ensureAccountConnected($account);

        $response = Http::withToken($account->access_token)
            ->delete("{$this->baseUrl}/{$account->waba_id}/message_templates", [
                'name' => $name,
            ]);

        if ($response->failed()) {
            $this->logError('deleteTemplate', $account, $response, ['name' => $name]);
            throw new \Exception('Failed to delete template from Meta: ' . $response->json('error.message', 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Ensures the account has minimum required credentials.
     */
    protected function ensureAccountConnected(WhatsAppAccount $account): void
    {
        if ($account->connection_status !== 'connected' || !$account->access_token || !$account->waba_id) {
            throw new \Exception('WhatsApp account is not fully connected or is missing credentials.');
        }
    }

    /**
     * Standardized internal logging for Meta API errors.
     */
    protected function logError(string $action, WhatsAppAccount $account, \Illuminate\Http\Client\Response $response, array $context = []): void
    {
        Log::error("Meta API Error ({$action}) logic for Company {$account->company_id}", [
            'waba_id' => $account->waba_id,
            'status' => $response->status(),
            'body' => $response->json(),
            'context' => $context,
        ]);
    }
}
