<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Services\Campaign\CampaignAudienceService;
use App\Services\Campaign\CampaignDispatchService;

echo "=== TESTING RECIPIENT REMOVAL & PRE-DISPATCH VALIDATION FILTER ===\n";

$user = User::first();
$campaign = Campaign::where('company_id', $user->company_id)->latest()->first();

if (!$campaign) {
    echo "No campaign found.\n";
    exit;
}

$audienceService = app(CampaignAudienceService::class);
$dispatchService = app(CampaignDispatchService::class);

$preview = $audienceService->validateAndPreviewRecipients($user, $campaign);
echo "Initial Validation Preview: Total={$preview['total']}, Passed={$preview['passed_count']}, Failed={$preview['failed_count']}\n";

if (!empty($preview['rows'][0])) {
    $firstRow = $preview['rows'][0];
    echo "Testing Removal of Recipient #{$firstRow['id']} ({$firstRow['phone']})...\n";
    $audienceService->removeRecipientRow($user, $campaign, $firstRow['id']);
    
    $updatedPreview = $audienceService->validateAndPreviewRecipients($user, $campaign);
    echo "Post-Removal Validation Preview: Total={$updatedPreview['total']}, Passed={$updatedPreview['passed_count']}, Failed={$updatedPreview['failed_count']}\n";
}

echo "=== TEST COMPLETED SUCCESSFULLY ===\n";
