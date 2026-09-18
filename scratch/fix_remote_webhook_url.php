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

echo "=== FIXING REMOTE WEBHOOK CALLBACK URL ===\n";
$correctWebhookUrl = "https://whatsapp-automate.empoweredtechinnovations.org/webhooks/whatsapp/meta";

try {
    $res = $client->patch('/api/v1/whatsapp/setup/account', [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ],
        'json' => [
            'waba_id' => '1448089013739297',
            'business_id' => '1060469103825419',
            'webhook_callback_url' => $correctWebhookUrl,
        ]
    ]);
    echo "Response:\n" . json_encode(json_decode((string)$res->getBody(), true), JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error updating account: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response body: " . (string)$e->getResponse()->getBody() . "\n";
    }
}
