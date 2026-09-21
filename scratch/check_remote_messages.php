<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';
$email = 'admin@platform.local';
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

// 1. Check Auth User
try {
    $meRes = $client->get('/api/v1/auth/me', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    echo "--- AUTH USER --- \n";
    echo (string)$meRes->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error auth me: " . $e->getMessage() . "\n\n";
}

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

// 3. Search for test conversation
echo "--- 2. SEARCH FOR PHONE 2347010894583 --- \n";
try {
    $chatsRes = $client->get('/api/v1/chats?search=2347010894583', [
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

