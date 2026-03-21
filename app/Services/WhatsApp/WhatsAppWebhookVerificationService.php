<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookVerificationService
{
    /**
     * Determine if the incoming request is a valid verification handshake.
     *
     * @param Request $request
     * @return bool
     */
    public function isValidVerificationRequest(Request $request): bool
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');

        if ($mode !== 'subscribe') {
            Log::warning('WhatsApp Webhook Verification failed: Invalid mode', ['mode' => $mode]);
            return false;
        }

        $expectedToken = $this->resolveExpectedToken();

        if (empty($expectedToken)) {
            Log::error('WhatsApp Webhook Verification failed: No expected verify token configured in system.');
            return false;
        }

        if ($token !== $expectedToken) {
            Log::warning('WhatsApp Webhook Verification failed: Token mismatch', [
                'provided' => $token,
                // Do not log the expected token in production for security, but we note the mismatch.
            ]);
            return false;
        }

        return true;
    }

    /**
     * Resolve the verification challenge from the request.
     *
     * @param Request $request
     * @return string
     */
    public function resolveChallenge(Request $request): string
    {
        return $request->query('hub_challenge', '');
    }

    /**
     * Resolves the expected token from config.
     * As per the platform architecture, we use a single platform-level token
     * because Meta's GET verification does not send identifiable WABA context.
     *
     * @return string
     */
    public function resolveExpectedToken(): string
    {
        return config('services.whatsapp.webhook_verify_token', '');
    }
}
