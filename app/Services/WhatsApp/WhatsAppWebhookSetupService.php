<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookSetupService
{
    /**
     * Retrieve webhook setup data for the user.
     */
    public function getSetupDataForUser(User $user): array
    {
        $account = $user->company->whatsappAccount;
        
        $status = $account ? $account->webhook_status : 'not_configured';
        $subStatus = $account ? $account->webhook_subscription_status : 'not_subscribed';
        
        return [
            'callback_url' => route('webhooks.whatsapp.meta.receive'),
            'verify_token' => config('services.whatsapp.webhook_verify_token', ''),
            'webhook_status' => $status,
            'webhook_subscription_status' => $subStatus,
            'webhook_verified_at' => $account?->webhook_verified_at,
            'webhook_last_checked_at' => $account?->webhook_last_checked_at,
            'webhook_last_error' => $account?->webhook_last_error,
            'has_connected_account' => $account && $account->waba_id && $account->access_token,
            'waba_id' => $account?->waba_id,
        ];
    }

    /**
     * Subscribe the App to the WABA using Graph API.
     */
    public function subscribeAppToWabaForUser(User $user): array
    {
        $account = $user->company->whatsappAccount;

        if (!$account || !$account->waba_id || !$account->access_token) {
            return [
                'success' => false,
                'message' => 'WhatsApp account is not connected properly.',
            ];
        }

        try {
            // Check current configuration or attempt subscription logic
            // To subscribe an app to a WABA, you issue a POST request to /{waba_id}/subscribed_apps
            // Requires System User access token, which we store in $account->access_token
            $url = "https://graph.facebook.com/" . config('services.whatsapp.graph_api_version', 'v21.0') . "/{$account->waba_id}/subscribed_apps";
            
            $response = Http::withToken($account->access_token)
                ->post($url);

            if ($response->successful()) {
                $account->update([
                    'webhook_subscription_status' => 'subscribed',
                    'webhook_subscribed_at' => now(),
                    'webhook_last_error' => null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Successfully subscribed app to WhatsApp Business Account.',
                ];
            }

            $error = $response->json('error.message') ?? 'Unknown error occurred during subscription.';
            
            $account->update([
                'webhook_subscription_status' => 'failed',
                'webhook_last_error' => $error,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to subscribe app: ' . $error,
            ];

        } catch (\Exception $e) {
            Log::error('Webhook Setup Error: ' . $e->getMessage());
            
            $account->update([
                'webhook_subscription_status' => 'failed',
                'webhook_last_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'System error during subscription process.',
            ];
        }
    }

    /**
     * Mark locally configured based on standard checks
     */
    public function refreshWebhookHealthForUser(User $user): array
    {
        $account = $user->company->whatsappAccount;
        if (!$account) return ['success' => false];

        // This is a placeholder local health check to refresh the 'verified' status manually.
        // In real cases, Meta hits us. We can just mark this account as 'verified' if we detect valid events.
        
        $account->update([
            'webhook_last_checked_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Status refreshed.'];
    }
}
