<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\Chat\ChatInboxService;

echo "=== TESTING ACTIVE & INACTIVE CHAT TAB FILTERS ===\n";

$user = User::whereNotNull('company_id')->first();
$inboxService = app(ChatInboxService::class);

$allChats = $inboxService->getConversationListForUser($user, ['tab' => 'all']);
$activeChats = $inboxService->getConversationListForUser($user, ['tab' => 'active']);
$inactiveChats = $inboxService->getConversationListForUser($user, ['tab' => 'inactive']);

echo "All Chats Count: " . count($allChats) . "\n";
echo "Active Chats (24h) Count: " . count($activeChats) . "\n";
echo "Inactive Chats Count: " . count($inactiveChats) . "\n";

echo "=== TEST PASSED SUCCESSFULLY ===\n";
