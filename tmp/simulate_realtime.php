<?php

use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatConversationResolverService;
use Illuminate\Support\Facades\Log;

// 1. Find a test phone number
$phoneNumber = WhatsAppPhoneNumber::first();

if (!$phoneNumber) {
    echo "No WhatsApp phone number found in DB.\n";
    exit(1);
}

echo "Using phone number: {$phoneNumber->phone_number} (ID: {$phoneNumber->id})\n";
echo "Company ID: {$phoneNumber->company_id}\n";

$service = app(ChatConversationResolverService::class);

$messageData = [
    'from' => '1234567890',
    'id' => 'simulated-' . uniqid(),
    'type' => 'text',
    'text' => [
        'body' => 'Simulated realtime message at ' . now()->toDateTimeString()
    ],
    'timestamp' => time(),
];

echo "Simulating inbound message...\n";

try {
    $service->resolveAndProcessInboundMessage($phoneNumber, $messageData, [
        'profile' => ['name' => 'Simulated User']
    ]);
    echo "Success! Check storage/logs/laravel.log for 'Realtime:' entries.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    Log::error("Simulated inbound message failed", ['error' => $e->getMessage()]);
}
