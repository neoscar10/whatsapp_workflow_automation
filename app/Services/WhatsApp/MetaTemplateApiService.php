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
     * Lists templates for a specific WABA ID.
     */
    public function listTemplates(WhatsAppAccount $account, array $params = []): array
    {
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
            'context' => $context, // Strip sensitive data if making generic, but usually payload is safe here.
        ]);
    }
}
