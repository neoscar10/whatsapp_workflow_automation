<?php

namespace App\Jobs\Webhooks;

use App\Models\Webhooks\CompanyWebhook;
use App\Models\Webhooks\CompanyWebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchCompanyWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10; // seconds

    public function __construct(
        public int $companyWebhookId,
        public string $eventType,
        public array $payload
    ) {}

    public function handle(): void
    {
        $webhook = CompanyWebhook::find($this->companyWebhookId);

        if (!$webhook || !$webhook->is_active) {
            return;
        }

        $payloadData = array_merge([
            'event' => $this->eventType,
            'timestamp' => now()->toIso8601String(),
        ], $this->payload);

        $jsonPayload = json_encode($payloadData);
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $webhook->secret);

        $statusCode = null;
        $responseBody = null;
        $errorMessage = null;
        $deliveredAt = null;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature-256' => $signature,
                'User-Agent' => 'WA-Cloud-Webhook/1.0',
            ])
            ->timeout(10)
            ->withBody($jsonPayload, 'application/json')
            ->post($webhook->url);

            $statusCode = $response->status();
            $responseBody = mb_substr($response->body(), 0, 5000); // store max 5000 chars

            if ($response->successful()) {
                $deliveredAt = now();
            } else {
                $errorMessage = "HTTP Error {$statusCode}";
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::warning("Company Webhook dispatch failed for URL [{$webhook->url}]: " . $e->getMessage());
        }

        CompanyWebhookDelivery::create([
            'company_webhook_id' => $webhook->id,
            'event_type' => $this->eventType,
            'payload' => $payloadData,
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'error_message' => $errorMessage,
            'attempt' => $this->attempts(),
            'delivered_at' => $deliveredAt,
        ]);
    }
}
