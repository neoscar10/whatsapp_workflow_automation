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
    public function sendTextMessage(string $phoneNumberId, string $accessToken, string $to, string $text, string $correlationId = 'N/A'): array
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
                Log::error("[{$correlationId}] WHATSAPP_SEND_FAILURE: {$error}", [
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
            Log::error("[{$correlationId}] WHATSAPP_SEND_EXCEPTION: " . $e->getMessage(), [
                'phone_id' => $phoneNumberId,
                'to' => $to,
                'file' => $e->getFile(),
                'line' => $e->getLine()
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
    public function sendTemplate(string $phoneNumberId, string $accessToken, string $to, string $templateName, string $languageCode = 'en_US', array $components = [], string $correlationId = 'N/A'): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$phoneNumberId}/messages";

        $templateData = [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
        ];

        if (!empty($components)) {
            $templateData['components'] = $components;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => $templateData
        ];

        Log::info("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_PAYLOAD: Final payload to provider", [
            'url' => $url,
            'payload' => $payload,
            'components_count' => count($components)
        ]);

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, $payload);

            $responseBody = $response->json();

            if ($response->failed()) {
                $error = $response->json('error.message', 'Unknown Meta API error');
                
                Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Meta API rejected template", [
                    'status_code' => $response->status(),
                    'error_message' => $error,
                    'error_code' => $response->json('error.code'),
                    'error_subcode' => $response->json('error.error_subcode'),
                    'response_body' => $responseBody,
                    'template' => $templateName,
                    'phone_id' => $phoneNumberId
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                    'response_body' => $responseBody
                ];
            }

            Log::info("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_SUCCESS: Meta API accepted template", [
                'status_code' => $response->status(),
                'message_id' => $response->json('messages.0.id'),
                'response_body' => $responseBody
            ]);

            return [
                'success' => true,
                'message_id' => $response->json('messages.0.id'),
                'data' => $responseBody
            ];

        } catch (\Exception $e) {
            Log::error("[{$correlationId}] WHATSAPP_TEMPLATE_SEND_FAILURE: Network or Runtime Exception", [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => __METHOD__
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

    /**
     * Initialize a resumable upload session for template-review headers.
     * Returns an upload session ID.
     */
    public function createResumableUpload(string $accessToken, string $appId, int $fileSize, string $fileType): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$appId}/uploads";
        
        $response = Http::withToken($accessToken)->post($url, [
            'file_length' => $fileSize,
            'file_type' => $fileType,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->json('error.message', 'Failed to create upload session')
            ];
        }

        return [
            'success' => true,
            'upload_session_id' => $response->json('id')
        ];
    }

    /**
     * Upload the actual file data to a resumable upload session.
     * Returns a 'h' handle for template creation.
     */
    public function uploadFileToSession(string $accessToken, string $uploadSessionId, $fileContents): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$uploadSessionId}";
        
        $response = Http::withToken($accessToken)
            ->withBody($fileContents, 'application/octet-stream')
            ->post($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->json('error.message', 'Failed to upload file data')
            ];
        }

        return [
            'success' => true,
            'h' => $response->json('h')
        ];
    }

    /**
     * Upload media for a message (simple upload).
     * Returns a media ID.
     */
    public function uploadMessageMedia(string $phoneNumberId, string $accessToken, $fileContents, string $filename, string $mimeType): array
    {
        $url = "{$this->baseUrl}/{$this->version}/{$phoneNumberId}/media";
        
        $response = Http::withToken($accessToken)
            ->attach('file', $fileContents, $filename, ['Content-Type' => $mimeType])
            ->post($url, [
                'messaging_product' => 'whatsapp',
            ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->json('error.message', 'Failed to upload message media')
            ];
        }

        return [
            'success' => true,
            'media_id' => $response->json('id')
        ];
    }
}
