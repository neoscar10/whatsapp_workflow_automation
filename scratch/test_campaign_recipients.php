<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';
$client = new Client(['base_uri' => $baseUrl, 'http_errors' => false, 'cookies' => true]);

$loginResponse = $client->post('/api/v1/auth/login', [
    'json' => [
        'email' => 'ca@ca.com',
        'password' => 'password'
    ],
    'headers' => [
        'Accept' => 'application/json'
    ]
]);

$loginBody = json_decode((string)$loginResponse->getBody(), true);
$token = $loginBody['data']['token'] ?? null;

$headers = [
    'Accept' => 'application/json',
    'Authorization' => 'Bearer ' . $token
];

echo "Fetching Campaign #42 details (/api/v1/campaigns/42)...\n";
$campaignResponse = $client->get('/api/v1/campaigns/42', [
    'headers' => $headers
]);

echo "Status Code: " . $campaignResponse->getStatusCode() . "\n";
$body = json_decode((string)$campaignResponse->getBody(), true);
print_r($body);
