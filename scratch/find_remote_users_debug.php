<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout'  => 15.0,
    'verify'   => false,
    'cookies'  => true,
]);

echo "=== INSPECTING REMOTE USERS & COMPANIES ===\n";

// Login as admin
$res = $client->post('/api/v1/auth/login', [
    'json' => ['email' => 'admin@platform.local', 'password' => 'password'],
    'headers' => ['Accept' => 'application/json']
]);
$json = json_decode((string)$res->getBody(), true);
$token = $json['data']['token'];

echo "Admin Token: " . substr($token, 0, 15) . "...\n";

// Check company profile with X-Company-ID: 6
try {
    $cRes = $client->get('/api/v1/company/profile', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'X-Company-ID' => 6,
            'Accept' => 'application/json',
        ]
    ]);
    echo "Company Profile (Company 6):\n" . (string)$cRes->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching company profile: " . $e->getMessage() . "\n";
}

// Check chats with X-Company-ID: 6
try {
    $chatsRes = $client->get('/api/v1/chats?per_page=5', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'X-Company-ID' => 6,
            'Accept' => 'application/json',
        ]
    ]);
    echo "Chats for Company 6:\n" . (string)$chatsRes->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching chats for Company 6: " . $e->getMessage() . "\n";
}
