<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$baseUrl = 'https://whatsapp-automate.empoweredtechinnovations.org';
$client = new Client([
    'base_uri' => $baseUrl,
    'http_errors' => false,
    'verify' => false, // In case of SSL verification quirks
]);

echo "=========================================================\n";
echo "REMOTE ENDPOINT AUDIT SCRIPT\n";
echo "Base URL: {$baseUrl}\n";
echo "=========================================================\n\n";

function testEndpoint(Client $client, string $name, string $method, string $path, array $headers = [], array $body = null) {
    $options = ['headers' => $headers];
    if ($body !== null) {
        $options['json'] = $body;
    }
    
    $start = microtime(true);
    $response = $client->request($method, $path, $options);
    $duration = round((microtime(true) - $start) * 1000, 2);
    
    $statusCode = $response->getStatusCode();
    $rawBody = (string)$response->getBody();
    $json = json_decode($rawBody, true);
    
    $isJson = json_last_error() === JSON_ERROR_NONE;
    $statusText = ($statusCode >= 200 && $statusCode < 400) ? 'SUCCESS' : (($statusCode == 422 || $statusCode == 404) ? 'VALIDATION/NOT_FOUND' : 'FAIL');
    
    echo sprintf("[%s] %-6s %-45s => HTTP %d (%s) [%s ms]\n", 
        $statusText, $method, $path, $statusCode, $isJson ? 'JSON' : 'HTML', $duration);
    
    if (!$isJson) {
        echo "  --> Warning: Non-JSON Response Body (First 150 chars): " . substr(strip_tags($rawBody), 0, 150) . "\n";
    } elseif ($statusCode >= 400 && $statusCode != 404 && $statusCode != 422) {
        echo "  --> Error Response: " . json_encode($json) . "\n";
    }
    
    return [
        'name' => $name,
        'method' => $method,
        'path' => $path,
        'status' => $statusCode,
        'is_json' => $isJson,
        'duration' => $duration,
        'response' => $json ?? substr($rawBody, 0, 200)
    ];
}

// -------------------------------------------------------------
// 1. AUDIT USER: ca@ca.com
// -------------------------------------------------------------
echo "1. AUDITING ACCOUNT: ca@ca.com\n";
echo "---------------------------------------------------------\n";

$loginRes = testEndpoint($client, 'Auth Login (CA)', 'POST', '/api/v1/auth/login', [
    'Accept' => 'application/json'
], [
    'email' => 'ca@ca.com',
    'password' => 'password'
]);

$caToken = $loginRes['response']['data']['token'] ?? null;

if (!$caToken) {
    echo "ERROR: Failed to obtain Bearer token for ca@ca.com!\n";
} else {
    echo "Successfully authenticated ca@ca.com. Token acquired.\n\n";
    $headers = [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $caToken
    ];

    // Auth & User Profile
    testEndpoint($client, 'Auth Me', 'GET', '/api/v1/auth/me', $headers);
    testEndpoint($client, 'Dashboard Stats', 'GET', '/api/v1/dashboard', $headers);

    // WhatsApp Setup & Accounts
    testEndpoint($client, 'WhatsApp Account Setup', 'GET', '/api/v1/whatsapp/setup/account', $headers);
    testEndpoint($client, 'Update WhatsApp Account (PATCH)', 'PATCH', '/api/v1/whatsapp/setup/account', $headers, [
        'webhook_callback_url' => 'https://example.com/ca-webhook'
    ]);
    $phoneRes = testEndpoint($client, 'List Phone Numbers', 'GET', '/api/v1/whatsapp/setup/phone-numbers', $headers);
    $firstPhoneId = $phoneRes['response']['data']['data'][0]['id'] ?? 1;
    testEndpoint($client, 'Update Phone Number (PATCH)', 'PATCH', "/api/v1/whatsapp/setup/phone-numbers/{$firstPhoneId}", $headers, [
        'display_name' => 'CA Business Number'
    ]);

    // WhatsApp Templates
    $tplRes = testEndpoint($client, 'List WhatsApp Templates', 'GET', '/api/v1/whatsapp/templates', $headers);
    testEndpoint($client, 'Get Template Categories Helper', 'GET', '/api/v1/whatsapp/templates/helpers/categories', $headers);
    testEndpoint($client, 'Get Template Languages Helper', 'GET', '/api/v1/whatsapp/templates/helpers/languages', $headers);
    
    // Create Template with Dynamic URL Button
    $createTplRes = testEndpoint($client, 'Create Dynamic Template', 'POST', '/api/v1/whatsapp/templates', $headers, [
        'name' => 'ca_dynamic_test_' . rand(100, 999),
        'category' => 'marketing',
        'language_code' => 'en',
        'header_type' => 'none',
        'body_text' => 'Hello {{1}}, track your order here:',
        'buttons' => [
            [
                'type' => 'url',
                'text' => 'Track Order',
                'url' => 'https://example.com/track/{{1}}'
            ]
        ]
    ]);
    
    $createdTplId = $createTplRes['response']['data']['id'] ?? null;
    if ($createdTplId) {
        testEndpoint($client, 'Show Created Template', 'GET', "/api/v1/whatsapp/templates/{$createdTplId}", $headers);
    }
    
    testEndpoint($client, 'Sync WhatsApp Templates from Meta', 'POST', '/api/v1/whatsapp/templates/sync', $headers);

    // Chats & Messages
    $chatsRes = testEndpoint($client, 'List Chats', 'GET', '/api/v1/chats', $headers);
    $firstConvId = $chatsRes['response']['data']['data'][0]['id'] ?? null;
    if ($firstConvId) {
        testEndpoint($client, 'Show Chat Details', 'GET', "/api/v1/chats/{$firstConvId}", $headers);
        testEndpoint($client, 'List Messages in Chat', 'GET', "/api/v1/chats/{$firstConvId}/messages", $headers);
    }

    // Contacts & Groups
    testEndpoint($client, 'List Contacts', 'GET', '/api/v1/contacts', $headers);
    testEndpoint($client, 'List Contact Tags', 'GET', '/api/v1/contact-tags', $headers);
    $groupsRes = testEndpoint($client, 'List Contact Groups', 'GET', '/api/v1/contact-groups', $headers);

    // Campaigns
    testEndpoint($client, 'List Campaigns', 'GET', '/api/v1/campaigns', $headers);
    testEndpoint($client, 'Campaign Helper Templates', 'GET', '/api/v1/campaigns/helpers/templates', $headers);
    testEndpoint($client, 'Campaign Personalization Fields', 'GET', '/api/v1/campaigns/helpers/personalization-fields', $headers);

    // Company & Verification
    testEndpoint($client, 'Company Verification', 'GET', '/api/v1/company/verification', $headers);
    testEndpoint($client, 'Company Profile', 'GET', '/api/v1/company/profile', $headers);

    // Wallet
    testEndpoint($client, 'Wallet Overview', 'GET', '/api/v1/wallet', $headers);
    testEndpoint($client, 'Wallet Transactions', 'GET', '/api/v1/wallet/transactions', $headers);
    testEndpoint($client, 'Wallet Funding Methods', 'GET', '/api/v1/wallet/funding-methods', $headers);

    // Webhooks
    testEndpoint($client, 'List Webhooks', 'GET', '/api/v1/webhooks', $headers);
    testEndpoint($client, 'Create Webhook with Invalid Event (Validation Test)', 'POST', '/api/v1/webhooks', $headers, [
        'name' => 'CA Invalid Webhook',
        'url' => 'https://example.com/webhook',
        'events' => ['invalid.event.name']
    ]);
}

echo "\n\n";

// -------------------------------------------------------------
// 2. AUDIT ADMIN USER: admin@platform.local
// -------------------------------------------------------------
echo "2. AUDITING ADMIN ACCOUNT: admin@platform.local\n";
echo "---------------------------------------------------------\n";

$adminLoginRes = testEndpoint($client, 'Auth Login (Admin)', 'POST', '/api/v1/auth/login', [
    'Accept' => 'application/json'
], [
    'email' => 'admin@platform.local',
    'password' => 'password'
]);

$adminToken = $adminLoginRes['response']['data']['token'] ?? null;

if (!$adminToken) {
    echo "ERROR: Failed to obtain Bearer token for admin@platform.local!\n";
} else {
    echo "Successfully authenticated admin@platform.local. Token acquired.\n\n";
    $adminHeaders = [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $adminToken
    ];

    testEndpoint($client, 'Admin Auth Me', 'GET', '/api/v1/auth/me', $adminHeaders);
    testEndpoint($client, 'Admin Dashboard', 'GET', '/api/v1/dashboard', $adminHeaders);
    testEndpoint($client, 'Admin WhatsApp Setup Account', 'GET', '/api/v1/whatsapp/setup/account', $adminHeaders);
    testEndpoint($client, 'Admin List Phone Numbers', 'GET', '/api/v1/whatsapp/setup/phone-numbers', $adminHeaders);
    testEndpoint($client, 'Admin List Templates', 'GET', '/api/v1/whatsapp/templates', $adminHeaders);
    testEndpoint($client, 'Admin List Contacts', 'GET', '/api/v1/contacts', $adminHeaders);
    testEndpoint($client, 'Admin List Campaigns', 'GET', '/api/v1/campaigns', $adminHeaders);
    testEndpoint($client, 'Admin Wallet Overview', 'GET', '/api/v1/wallet', $adminHeaders);
}

echo "\n=========================================================\n";
echo "AUDIT COMPLETE\n";
echo "=========================================================\n";
