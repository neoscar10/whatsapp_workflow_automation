<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';

$client = new Client([
    'base_uri' => $baseUrl,
    'timeout'  => 15.0,
    'verify'   => false,
]);

echo "=== SENDING SIMULATED META INBOUND WEBHOOK PAYLOAD TO REMOTE SERVER ===\n";

$payload = [
    'object' => 'whatsapp_business_account',
    'entry' => [
        [
            'id' => '1448089013739297', // WABA ID from remote company account
            'changes' => [
                [
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'display_phone_number' => '+919818923734',
                            'phone_number_id' => '1060469103825419',
                        ],
                        'contacts' => [
                            [
                                'profile' => [
                                    'name' => 'Jeremiah',
                                ],
                                'wa_id' => '2347010894583',
                            ]
                        ],
                        'messages' => [
                            [
                                'from' => '2347010894583',
                                'id' => 'wamid.LIVE_TEST_' . time() . '_' . rand(1000, 9999),
                                'timestamp' => (string)time(),
                                'type' => 'text',
                                'text' => [
                                    'body' => 'Test inbound message live test proof',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

try {
    $res = $client->post('/webhooks/whatsapp/meta', [
        'json' => $payload,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]
    ]);
    echo "Webhook POST Status: " . $res->getStatusCode() . "\n";
    echo "Webhook Response Body: " . (string)$res->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Error calling webhook: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response body: " . (string)$e->getResponse().getBody() . "\n";
    }
}
