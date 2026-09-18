<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';
$email = 'ca@ca.com';
$password = 'password';

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout'  => 15.0,
    'verify'   => false,
]);

// 1. Login
$loginRes = $client->post('/api/v1/auth/login', [
    'json' => ['email' => $email, 'password' => $password],
    'headers' => ['Accept' => 'application/json']
]);
$json = json_decode((string)$loginRes->getBody(), true);
$token = $json['data']['token'];

echo "=== REMOTE API CHECK ===\n";
echo "Token acquired.\n\n";

// 2. Fetch Connected Phone Numbers
echo "--- 1. CONNECTED PHONE NUMBERS --- \n";
try {
    $phonesRes = $client->get('/api/v1/whatsapp/setup/phone-numbers', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    $pJson = json_decode((string)$phonesRes->getBody(), true);
    echo json_encode($pJson, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching phone numbers: " . $e->getMessage() . "\n\n";
}

// 3. Fetch Recent Conversations
echo "--- 2. RECENT CONVERSATIONS --- \n";
try {
    $chatsRes = $client->get('/api/v1/chats', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    $cJson = json_decode((string)$chatsRes->getBody(), true);
    echo json_encode($cJson, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching chats: " . $e->getMessage() . "\n\n";
}
