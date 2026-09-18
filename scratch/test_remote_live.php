<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';
$email = 'ca@ca.com';
$password = 'password';

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout'  => 15.0,
    'verify'   => false, // Skip SSL verification if self-signed or proxy issues
]);

echo "=== STEP 1: LOGIN TO REMOTE SERVER ===\n";
echo "Attempting login to $baseUrl/api/v1/auth/login ...\n";

try {
    $response = $client->post('/api/v1/auth/login', [
        'json' => [
            'email' => $email,
            'password' => $password,
        ],
        'headers' => [
            'Accept' => 'application/json',
        ]
    ]);

    $statusCode = $response->getStatusCode();
    $body = (string) $response->getBody();
    echo "Login Response Status: $statusCode\n";
    $json = json_decode($body, true);
    echo "Login Data: " . json_encode($json, JSON_PRETTY_PRINT) . "\n\n";

    $token = $json['data']['token'] ?? $json['token'] ?? $json['access_token'] ?? null;
    $user = $json['data']['user'] ?? $json['user'] ?? null;
    $companyId = $user['company_id'] ?? $json['data']['company_id'] ?? 1;

    if (!$token) {
        echo "FAILED to retrieve token from login response!\n";
        exit(1);
    }

    echo "Token acquired successfully: " . substr($token, 0, 15) . "...\n";
    echo "Company ID: $companyId\n\n";

    echo "=== STEP 2: TEST REMOTE BROADCASTING AUTH ENDPOINT ===\n";
    $channelName = "private-company.{$companyId}.chats";
    $socketId = "12345.67890";

    echo "Requesting channel auth for '$channelName' with socket_id '$socketId'...\n";

    $authResponse = $client->post('/api/v1/broadcasting/auth', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ],
        'form_params' => [
            'socket_id' => $socketId,
            'channel_name' => $channelName,
        ],
    ]);

    echo "Broadcasting Auth Status: " . $authResponse->getStatusCode() . "\n";
    echo "Broadcasting Auth Body: " . (string)$authResponse->getBody() . "\n\n";

    echo "=== STEP 3: TEST LEGACY /broadcasting/auth ENDPOINT WITH BEARER TOKEN ===\n";
    try {
        $legacyAuthResponse = $client->post('/broadcasting/auth', [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'socket_id' => $socketId,
                'channel_name' => $channelName,
            ],
        ]);
        echo "Legacy Broadcasting Auth Status: " . $legacyAuthResponse->getStatusCode() . "\n";
        echo "Legacy Broadcasting Auth Body: " . (string)$legacyAuthResponse->getBody() . "\n\n";
    } catch (\Exception $e) {
        echo "Legacy /broadcasting/auth failed: " . $e->getMessage() . "\n\n";
    }

} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "HTTP Client Exception: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response Body: " . (string) $e->getResponse()->getBody() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
