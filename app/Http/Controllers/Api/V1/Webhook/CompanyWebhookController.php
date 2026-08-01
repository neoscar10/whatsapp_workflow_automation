<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Webhook\StoreCompanyWebhookRequest;
use App\Http\Requests\Api\V1\Webhook\UpdateCompanyWebhookRequest;
use App\Http\Resources\Api\V1\Webhook\CompanyWebhookResource;
use App\Http\Resources\Api\V1\Webhook\CompanyWebhookDeliveryResource;
use App\Models\Webhooks\CompanyWebhook;
use App\Models\Webhooks\CompanyWebhookDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyWebhookController extends Controller
{
    use RespondsWithApiResponse;

    /**
     * Display a listing of the company's webhooks.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $perPage = $request->query('per_page', 15);
        $webhooks = CompanyWebhook::where('company_id', $company->id)
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(
            CompanyWebhookResource::collection($webhooks)->response()->getData(true),
            'Outbound webhooks retrieved successfully.'
        );
    }

    /**
     * Store a newly created webhook in storage.
     */
    public function store(StoreCompanyWebhookRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        try {
            $data = $request->validated();
            
            $webhook = CompanyWebhook::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'url' => $data['url'],
                'events' => $data['events'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $this->successResponse(
                new CompanyWebhookResource($webhook),
                'Webhook endpoint created successfully.',
                201
            );
        } catch (\Exception $e) {
            Log::error('API Webhook Creation Error', ['message' => $e->getMessage(), 'company_id' => $company->id]);
            return $this->errorResponse('Failed to create webhook endpoint: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified webhook.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        return $this->successResponse(
            new CompanyWebhookResource($webhook),
            'Webhook endpoint details retrieved successfully.'
        );
    }

    /**
     * Update the specified webhook in storage.
     */
    public function update(UpdateCompanyWebhookRequest $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        try {
            $data = $request->validated();
            $webhook->update(array_filter([
                'name' => $data['name'] ?? null,
                'url' => $data['url'] ?? null,
                'events' => $data['events'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : null,
            ], function ($val) { return $val !== null; }));

            return $this->successResponse(
                new CompanyWebhookResource($webhook),
                'Webhook endpoint updated successfully.'
            );
        } catch (\Exception $e) {
            Log::error('API Webhook Update Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to update webhook endpoint.', [], 500);
        }
    }

    /**
     * Remove the specified webhook from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        try {
            $webhook->delete();
            return $this->successResponse([], 'Webhook endpoint deleted successfully.');
        } catch (\Exception $e) {
            Log::error('API Webhook Deletion Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to delete webhook endpoint.', [], 500);
        }
    }

    /**
     * Toggle the status of the specified webhook.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        try {
            $webhook->update(['is_active' => !$webhook->is_active]);

            return $this->successResponse(
                new CompanyWebhookResource($webhook),
                'Webhook status updated successfully.'
            );
        } catch (\Exception $e) {
            Log::error('API Webhook Status Toggle Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to update status.', [], 500);
        }
    }

    /**
     * Send a test ping to the webhook URL.
     */
    public function ping(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        $testPayload = [
            'event' => 'ping.test',
            'timestamp' => now()->toIso8601String(),
            'message' => 'This is a test webhook payload from WhatsApp Cloud Panel API.',
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

            $pingResult = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'duration_ms' => $durationMs,
                'response_body' => mb_substr($response->body(), 0, 1000),
                'error' => $response->successful() ? null : 'Received non-2xx status code',
            ];

            return $this->successResponse($pingResult, 'Test ping executed.');
        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $pingResult = [
                'success' => false,
                'status_code' => null,
                'duration_ms' => $durationMs,
                'response_body' => null,
                'error' => $e->getMessage(),
            ];

            return $this->successResponse($pingResult, 'Test ping executed with exception.');
        }
    }

    /**
     * Display a listing of HTTP delivery logs for the specified webhook.
     */
    public function logs(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $webhook = CompanyWebhook::where('company_id', $company->id)->find($id);

        if (!$webhook) {
            return $this->errorResponse('Webhook endpoint not found.', [], 404);
        }

        $perPage = $request->query('per_page', 15);
        $logs = CompanyWebhookDelivery::where('company_webhook_id', $webhook->id)
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(
            CompanyWebhookDeliveryResource::collection($logs)->response()->getData(true),
            'Webhook delivery logs retrieved successfully.'
        );
    }
}
