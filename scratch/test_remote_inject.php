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

$loginRes = $client->post('/api/v1/auth/login', [
    'json' => ['email' => $email, 'password' => $password],
    'headers' => ['Accept' => 'application/json']
]);
$json = json_decode((string)$loginRes->getBody(), true);
$token = $json['data']['token'];

echo "=== INJECTING INBOUND MESSAGE ON REMOTE SERVER VIA API ===\n";

try {
    $res = $client->post('/api/v1/chats/inject-message', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ],
        'json' => [
            'phone_number' => '+919818923734',
            'direction' => 'inbound',
            'type' => 'text',
            'body' => 'Live test inbound message from phone test script',
        ]
    ]);

    echo "Status Code: " . $res->getStatusCode() . "\n";
    echo "Response: " . (string)$res->getBody() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response body: " . (string)$e->getResponse()->getBody() . "\n";
    }
}
