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

echo "=== CHECK REMOTE ACCOUNT SETUP & WEBHOOK CONFIG ===\n";

try {
    $res = $client->get('/api/v1/whatsapp/setup/account', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    echo "Account Setup Data:\n" . json_encode(json_decode((string)$res->getBody(), true), JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
