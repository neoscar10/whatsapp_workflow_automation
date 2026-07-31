<?php

namespace App\Services\Notifications;

use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobilePushNotificationService
{
    /**
     * Send a mobile push notification to a collection of device tokens.
     *
     * @param array|\Illuminate\Support\Collection $tokens
     * @param string $title
     * @param string $body
     * @param array $dataPayload
     * @return array
     */
    public function sendPushToTokens($tokens, string $title, string $body, array $dataPayload = []): array
    {
        if (empty($tokens)) {
            return ['sent' => 0, 'failed' => 0];
        }

        // Extract token strings
        if ($tokens instanceof \Illuminate\Support\Collection) {
            $first = $tokens->first();
            if (is_object($first) && isset($first->device_token)) {
                $tokenStrings = $tokens->pluck('device_token')->filter()->toArray();
            } else {
                $tokenStrings = $tokens->filter()->values()->toArray();
            }
        } else {
            $tokenStrings = (array) $tokens;
        }

        if (empty($tokenStrings)) {
            return ['sent' => 0, 'failed' => 0];
        }

        // Try FCM v1 API with Service Account first
        $credentialsPath = config('services.firebase.credentials') ?? env('FIREBASE_CREDENTIALS', 'storage/app/firebase/service-account.json');
        
        if (!is_absolute_path($credentialsPath)) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (file_exists($credentialsPath)) {
            return $this->sendViaFcmV1($credentialsPath, $tokenStrings, $title, $body, $dataPayload);
        }

        // Fallback to legacy FCM Server Key if configured
        $fcmServerKey = config('services.fcm.server_key') ?? env('FCM_SERVER_KEY');
        if (!empty($fcmServerKey)) {
            return $this->sendViaLegacyFcm($fcmServerKey, $tokenStrings, $title, $body, $dataPayload);
        }

        Log::info("No Firebase Service Account or FCM Server Key configured. Skipping mobile push notification to " . count($tokenStrings) . " device(s).");
        return ['sent' => 0, 'failed' => 0, 'status' => 'credentials_missing'];
    }

    /**
     * Send push using FCM HTTP v1 REST API with Google OAuth2 Service Account.
     */
    protected function sendViaFcmV1(string $credentialsPath, array $tokens, string $title, string $body, array $dataPayload): array
    {
        try {
            $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $serviceAccount['project_id'] ?? null;
            
            if (!$projectId) {
                Log::error("FCM v1 Error: Invalid service account file (missing project_id).");
                return ['sent' => 0, 'failed' => count($tokens)];
            }

            $accessToken = $this->getAccessTokenFromServiceAccount($serviceAccount);
            if (!$accessToken) {
                Log::error("FCM v1 Error: Could not obtain OAuth2 access token from service account.");
                return ['sent' => 0, 'failed' => count($tokens)];
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_map('strval', array_merge([
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ], $dataPayload)),
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'whatsapp_messages',
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ],
                            ],
                        ],
                    ],
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(5)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    Log::warning("FCM v1 Push failed for token [{$token}]: Status " . $response->status() . " Body: " . $response->body());
                }
            }

            return ['sent' => $sentCount, 'failed' => $failedCount];
        } catch (\Exception $e) {
            Log::error("FCM v1 Exception: " . $e->getMessage());
            return ['sent' => 0, 'failed' => count($tokens)];
        }
    }

    /**
     * Legacy FCM HTTP send fallback.
     */
    protected function sendViaLegacyFcm(string $fcmServerKey, array $tokens, string $title, string $body, array $dataPayload): array
    {
        $sentCount = 0;
        $failedCount = 0;

        foreach ($tokens as $token) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $fcmServerKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(5)
                ->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $token,
                    'priority' => 'high',
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                    'data' => array_map('strval', array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ], $dataPayload)),
                ]);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        return ['sent' => $sentCount, 'failed' => $failedCount];
    }

    /**
     * Obtain Google OAuth2 access token using RS256 JWT assertion.
     */
    protected function getAccessTokenFromServiceAccount(array $serviceAccount): ?string
    {
        $cacheKey = 'fcm_v1_access_token_' . md5($serviceAccount['client_email'] ?? 'default');

        return Cache::remember($cacheKey, 3300, function () use ($serviceAccount) {
            $clientEmail = $serviceAccount['client_email'] ?? null;
            $privateKey = $serviceAccount['private_key'] ?? null;

            if (!$clientEmail || !$privateKey) {
                return null;
            }

            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $claims = [
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ];

            $base64Header = $this->base64UrlEncode(json_encode($header));
            $base64Claims = $this->base64UrlEncode(json_encode($claims));

            $signatureInput = $base64Header . '.' . $base64Claims;
            $signature = '';

            $privateKeyRes = openssl_pkey_get_private($privateKey);
            if (!$privateKeyRes) {
                Log::error("Failed to parse private key for FCM service account.");
                return null;
            }

            if (!openssl_sign($signatureInput, $signature, $privateKeyRes, OPENSSL_ALGO_SHA256)) {
                Log::error("Failed to sign JWT for FCM service account.");
                return null;
            }

            $base64Signature = $this->base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64Signature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("Failed to get Google OAuth2 token: " . $response->body());
            return null;
        });
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('is_absolute_path')) {
    function is_absolute_path($path) {
        if (!is_string($path) || $path === '') return false;
        return $path[0] === '/' || $path[0] === '\\' || (strlen($path) > 1 && $path[1] === ':');
    }
}
