<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\WhatsAppWebhookEventService;
use App\Services\WhatsApp\WhatsAppWebhookVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle the Meta Webhook Verification handshake (GET)
     */
    public function verify(Request $request, WhatsAppWebhookVerificationService $verificationService)
    {
        \Illuminate\Support\Facades\Log::info('WhatsApp Webhook: RECEIVED HANDSHAKE (GET)', [
            'query' => $request->query(),
            'ip' => $request->ip(),
        ]);

        if ($verificationService->isValidVerificationRequest($request)) {
            $challenge = $verificationService->resolveChallenge($request);
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['error' => 'Invalid verification request'], 403);
    }

    /**
     * Handle incoming webhooks from Meta (POST)
     */
    public function receive(Request $request, WhatsAppWebhookEventService $eventService)
    {
        $payload = $request->all();
        
        \Illuminate\Support\Facades\Log::info('WhatsApp Webhook: Received POST payload', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'payload' => $payload
        ]);

        // Pass payload to event service for async or fast processing.
        $eventService->handle($payload);

        return response()->json(['status' => 'ok'], 200);
    }
}
