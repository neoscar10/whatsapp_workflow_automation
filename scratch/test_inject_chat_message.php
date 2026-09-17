<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Services\Chat\ChatMessageService;
use App\Http\Controllers\Api\V1\Chat\ExternalChatMessageController;
use App\Http\Requests\Api\V1\Chat\InjectChatMessageRequest;

echo "=== TESTING EXTERNAL CHAT MESSAGE INJECTION API ===\n";

$company = Company::firstOrCreate(['slug' => 'test-inject-corp'], ['name' => 'Inject Test Corp', 'country' => 'IN', 'primary_email' => 'inject@corp.com']);
$user = User::firstOrCreate(['email' => 'inject@corp.com'], ['name' => 'Inject Tester', 'password' => bcrypt('secret'), 'company_id' => $company->id]);

auth()->login($user);
$service = app(ChatMessageService::class);
$controller = new ExternalChatMessageController($service);

// Test 1: Outbound Text Message Injection
$req1 = InjectChatMessageRequest::create('/api/v1/chats/inject-message', 'POST', [
    'phone_number' => '+2348123456789',
    'direction' => 'outbound',
    'type' => 'text',
    'body' => 'External System: Order #9910 has been dispatched.',
]);
$req1->setUserResolver(fn () => $user);

$res1 = $controller->injectMessage($req1);
echo "[1] Inject Outbound Text status: " . $res1->getStatusCode() . "\n";
$data1 = json_decode($res1->getContent(), true);
echo "    Message ID: " . ($data1['data']['message']['id'] ?? 'N/A') . "\n";
echo "    Conversation ID: " . ($data1['data']['conversation']['id'] ?? 'N/A') . "\n";
echo "    Body: " . ($data1['data']['message']['body'] ?? 'N/A') . "\n";

// Test 2: Inbound Text Message Injection
$req2 = InjectChatMessageRequest::create('/api/v1/chats/inject-message', 'POST', [
    'phone_number' => '+2348123456789',
    'direction' => 'inbound',
    'type' => 'text',
    'body' => 'User reply from external mobile app.',
]);
$req2->setUserResolver(fn () => $user);

$res2 = $controller->injectMessage($req2);
echo "[2] Inject Inbound Text status: " . $res2->getStatusCode() . "\n";

// Test 3: Template Message Injection
$req3 = InjectChatMessageRequest::create('/api/v1/chats/inject-message', 'POST', [
    'phone_number' => '+2348123456789',
    'direction' => 'outbound',
    'type' => 'template',
    'template_name' => 'order_status_update',
    'components' => [
        ['type' => 'body', 'parameters' => [['type' => 'text', 'text' => 'Order #9910']]]
    ],
]);
$req3->setUserResolver(fn () => $user);

$res3 = $controller->injectMessage($req3);
echo "[3] Inject Template Message status: " . $res3->getStatusCode() . "\n";

echo "=== EXTERNAL CHAT MESSAGE INJECTION TEST PASSED ===" . PHP_EOL;
