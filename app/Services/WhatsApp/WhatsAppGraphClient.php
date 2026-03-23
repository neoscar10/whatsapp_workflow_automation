<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGraphClient
{
    protected string $baseUrl = 'https://graph.facebook.com';
    protected string $version;

    public function __construct()
    {
        $this->version = config('services.whatsapp.version', 'v21.0');
    }

    /**
     * Get phone numbers associated with a WABA ID.
     */
    public function getPhoneNumbers(string $wabaId, string $accessToken): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$wabaId}/phone_numbers";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url);

            if ($response->failed()) {
                $error = $response->json('error.message', 'Unknown Meta API error');
                Log::error("WhatsApp API Error (getPhoneNumbers): {$error}", [
                    'waba_id' => $wabaId,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status()
                ];
            }

            return [
                'success' => true,
                'data' => $response->json('data', [])
            ];

        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception (getPhoneNumbers): " . $e->getMessage(), [
                'waba_id' => $wabaId
            ]);

            return [
                'success' => false,
                'error' => "Network or connection error while contacting Meta."
            ];
        }
    }

    /**
     * Debug an access token to verify permissions (Optional helper).
     */
    public function debugToken(string $inputToken, string $accessToken): array
    {
        $url = "{$this->baseUrl}/debug_token";

        $response = Http::get($url, [
            'input_token' => $inputToken,
            'access_token' => $accessToken
        ]);

        return $response->json();
    }
}
