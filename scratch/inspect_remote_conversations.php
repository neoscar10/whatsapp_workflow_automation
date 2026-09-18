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

echo "=== CHECKING ALL CHATS ON REMOTE SERVER FOR RECENT MESSAGES ===\n";

for ($page = 1; $page <= 3; $page++) {
    $res = $client->get("/api/v1/chats?page=$page", [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ]
    ]);
    $data = json_decode((string)$res->getBody(), true);
    $chats = $data['data']['data'] ?? [];

    foreach ($chats as $chat) {
        $lastMsg = $chat['last_message'] ?? null;
        $updatedAt = $chat['updated_at'] ?? '';
        $phone = $chat['contact_phone'] ?? '';
        $name = $chat['contact_name'] ?? '';

        echo "Chat #{$chat['id']} | Phone: $phone | Name: $name | Last Updated: $updatedAt\n";
        if ($lastMsg) {
            echo "   -> Last Msg ID: {$lastMsg['id']} | Direction: {$lastMsg['direction']} | Status: {$lastMsg['status']} | Created: {$lastMsg['created_at']}\n";
            echo "   -> Body: {$lastMsg['body']}\n";
        }
        echo "----------------------------------------------------------------------\n";
    }
}
