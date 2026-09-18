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

$loginRes = $client->post('/api/v1/auth/login', [
    'json' => ['email' => $email, 'password' => $password],
    'headers' => ['Accept' => 'application/json']
]);
$json = json_decode((string)$loginRes->getBody(), true);
$token = $json['data']['token'];

echo "=== INSPECT CHAT #95 ===\n";

try {
    $res = $client->get('/api/v1/chats/95', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    echo "Chat 95 Data:\n" . (string)$res->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching chat 95: " . $e->getMessage() . "\n\n";
}

try {
    $msgRes = $client->get('/api/v1/chats/95/messages', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    echo "Chat 95 Messages:\n" . (string)$msgRes->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error fetching chat 95 messages: " . $e->getMessage() . "\n\n";
}
