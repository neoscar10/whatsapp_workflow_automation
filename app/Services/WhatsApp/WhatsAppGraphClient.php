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
     * Get apps subscribed to a WABA's webhooks.
     */
    public function getSubscribedApps(string $wabaId, string $accessToken): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$wabaId}/subscribed_apps";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->get($url);

            if ($response->failed()) {
                $error = $response->json('error.message', 'Unknown Meta API error');
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
            Log::error("WhatsApp API Exception (getSubscribedApps): " . $e->getMessage());
            return [
                'success' => false,
                'error' => "Network error while checking subscription."
            ];
        }
    }

    /**
     * Send a text message via WhatsApp Cloud API.
     */
    public function sendTextMessage(string $phoneNumberId, string $accessToken, string $to, string $text): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $text]
                ]);

            if ($response->failed()) {
                $error = $response->json('error.message', 'Unknown Meta API error');
                Log::error("WhatsApp API Error (sendTextMessage): {$error}", [
                    'phone_id' => $phoneNumberId,
                    'to' => $to,
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
                'message_id' => $response->json('messages.0.id'),
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception (sendTextMessage): " . $e->getMessage(), [
                'phone_id' => $phoneNumberId,
                'to' => $to
            ]);

            return [
                'success' => false,
                'error' => "Network or connection error while sending message."
            ];
        }
    }

    /**
     * Send a template message via WhatsApp Cloud API.
     */
    public function sendTemplate(string $phoneNumberId, string $accessToken, string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => $languageCode],
                        'components' => $components
                    ]
                ]);

            if ($response->failed()) {
                $error = $response->json('error.message', 'Unknown Meta API error');
                Log::error("WhatsApp API Error (sendTemplate): {$error}", [
                    'phone_id' => $phoneNumberId,
                    'to' => $to,
                    'template' => $templateName,
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
                'message_id' => $response->json('messages.0.id'),
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception (sendTemplate): " . $e->getMessage(), [
                'phone_id' => $phoneNumberId,
                'to' => $to
            ]);

            return [
                'success' => false,
                'error' => "Network or connection error while sending template."
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
