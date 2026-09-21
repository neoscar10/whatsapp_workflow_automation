<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout'  => 15.0,
    'verify'   => false,
]);

// Login as admin to inspect users/companies if endpoint exists, or test login credentials
$adminRes = $client->post('/api/v1/auth/login', [
    'json' => ['email' => 'admin@platform.local', 'password' => 'password'],
    'headers' => ['Accept' => 'application/json']
]);

$adminJson = json_decode((string)$adminRes->getBody(), true);
$adminToken = $adminJson['data']['token'] ?? null;

echo "Admin Token: " . substr($adminToken ?? '', 0, 15) . "...\n\n";

// Try logging in with various email combinations
$testEmails = [
    'ca@ca.com',
    'ca@gmail.com',
    'ca@example.com',
    'user@ca.com',
    'ca@whatsapp.com',
    'lalalitti@ca.com',
];

foreach ($testEmails as $email) {
    try {
        $res = $client->post('/api/v1/auth/login', [
            'json' => ['email' => $email, 'password' => 'password'],
            'headers' => ['Accept' => 'application/json']
        ]);
        echo "SUCCESS LOGIN for $email: " . (string)$res->getBody() . "\n";
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        echo "Failed login for $email: " . $e->getResponse()->getStatusCode() . "\n";
    }
}
