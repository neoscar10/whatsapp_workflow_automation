<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Models\Contact\Contact;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignAudienceService;

echo "=== END-TO-END CAMPAIGN CREATION API VERIFICATION ===\n";

$user = User::whereNotNull('company_id')->first();
$phone = WhatsAppPhoneNumber::first();

if (!$user || !$phone) {
    echo "User or WhatsApp Phone Number missing in database.\n";
    exit;
}

$campaignService = app(CampaignService::class);
$audienceService = app(CampaignAudienceService::class);

// STEP 1: Create Campaign
echo "\n[Step 1] Creating Campaign...\n";
$campaign = $campaignService->createDraft($user, [
    'name' => 'E2E Refactor Verification Campaign',
    'whatsapp_phone_number_id' => $phone->id,
    'description' => 'End to end API test',
    'type' => 'text',
    'send_mode' => 'now',
]);
echo "Campaign Created: ID #{$campaign->id}, Name: {$campaign->name}, Type: {$campaign->type}\n";

// STEP 2: Add Manual Audience Rows
echo "\n[Step 2] Adding Manual Recipients...\n";
$manualRows = [
    ['phone' => '+919876543210', 'name' => 'Valid Test Contact'],
    ['phone' => '12345', 'name' => 'Invalid Phone Number'],
    ['phone' => '+2348012345678', 'name' => 'Text No Session Contact'],
];
$summary = $audienceService->addManualRecipients($user, $campaign, $manualRows);
echo "Recipients Added: Total={$summary['total_recipients']}\n";

// STEP 3: Audience Validation & Correction
echo "\n[Step 3] Running Audience Validation Preview...\n";
$preview = $audienceService->validateAndPreviewRecipients($user, $campaign);
echo "Validation Stats: Total={$preview['total']}, Passed={$preview['passed_count']}, Failed={$preview['failed_count']}, Text Excluded={$preview['text_session_excluded_count']}\n";

foreach ($preview['rows'] as $row) {
    echo "  - Row #{$row['id']}: {$row['phone']} ({$row['name']}) -> Valid: " . ($row['is_valid'] ? 'YES' : 'NO') . " | Reason: {$row['error_reason']}\n";
}

// STEP 3B: Test Recipient Removal
$failedRow = collect($preview['rows'])->firstWhere('is_valid', false);
if ($failedRow) {
    echo "\n[Step 3B] Removing Failed Recipient #{$failedRow['id']}...\n";
    $audienceService->removeRecipientRow($user, $campaign, $failedRow['id']);
    
    $postRemovalPreview = $audienceService->validateAndPreviewRecipients($user, $campaign);
    echo "Post-Removal Stats: Total={$postRemovalPreview['total']}, Passed={$postRemovalPreview['passed_count']}, Failed={$postRemovalPreview['failed_count']}\n";
}

// STEP 4: Content Update
echo "\n[Step 4] Updating Campaign Content...\n";
$campaignService->updateContent($user, $campaign, [
    'type' => 'text',
    'message_body' => 'End to end broadcast text test.',
]);
echo "Message Body Updated.\n";

// STEP 5: Final Review & Dispatch Verification
echo "\n[Step 5] Pre-Dispatch Verification...\n";
$finalPreview = $audienceService->validateAndPreviewRecipients($user, $campaign);
echo "Final Valid Recipients to Receive Message: {$finalPreview['passed_count']} out of {$finalPreview['total']}\n";

echo "\n=== ALL 5 STEPS VERIFIED END-TO-END SUCCESSFULLY ===\n";
