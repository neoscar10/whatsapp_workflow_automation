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

echo "=== LIVE VERIFICATION OF REMOTE WEBHOOK SETUP ===\n";

$res = $client->get('/api/v1/whatsapp/setup/account', [
    'headers' => [
        'Authorization' => "Bearer $token",
        'Accept' => 'application/json',
    ]
]);
$data = json_decode((string)$res->getBody(), true);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
