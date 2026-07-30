<?php

namespace App\Services\Notifications;

use App\Models\UserDeviceToken;
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

        $fcmServerKey = config('services.fcm.server_key') ?? env('FCM_SERVER_KEY');

        if (empty($fcmServerKey)) {
            Log::info("FCM Server Key not configured. Skipping mobile push notification to " . count($tokens) . " device(s).");
            return ['sent' => 0, 'failed' => 0, 'status' => 'fcm_key_missing'];
        }

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

        $sentCount = 0;
        $failedCount = 0;

        foreach ($tokenStrings as $token) {
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
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                    ], $dataPayload),
                ]);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    Log::warning("FCM Push notification failed for token [{$token}]: Status " . $response->status());
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::error("FCM Push exception for token [{$token}]: " . $e->getMessage());
            }
        }

        return [
            'sent' => $sentCount,
            'failed' => $failedCount,
        ];
    }
}
