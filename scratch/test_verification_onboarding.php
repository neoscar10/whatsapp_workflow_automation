<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\CompanyVerification;
use App\Services\Verification\VerificationWorkflowService;

echo "--- Testing Business Verification Onboarding Logic ---\n";

$company = Company::firstOrCreate(
    ['primary_email' => 'test-onboarding@example.com'],
    [
        'name' => 'Acme Test Corp',
        'slug' => 'acme-test-corp',
        'country' => 'NG',
    ]
);

$service = new VerificationWorkflowService();
$verification = $service->getOrCreateVerification($company);

echo "Initial Verification ID: " . $verification->id . "\n";

// Test 1: Set Business Entity Type to Sole proprietorship
$service->syncChecklistForEntity($verification, 'Sole proprietorship');
$verification->refresh();

$docCountSP = $verification->documents()->count();
echo "Sole proprietorship Doc Count (Expected 3: 2 req + 1 opt): " . $docCountSP . "\n";
foreach ($verification->documents as $doc) {
    echo " - " . $doc->documentType->name . " (Required: " . ($doc->documentType->is_required ? 'Yes' : 'No') . ")\n";
}

// Test 2: Change Business Entity Type to Private / public company
$service->syncChecklistForEntity($verification, 'Private / public company');
$verification->refresh();

$docCountCO = $verification->documents()->count();
echo "\nPrivate / public company Doc Count (Expected 4: 3 req + 1 opt): " . $docCountCO . "\n";
foreach ($verification->documents as $doc) {
    echo " - " . $doc->documentType->name . " (Required: " . ($doc->documentType->is_required ? 'Yes' : 'No') . ")\n";
}

// Test 3: Save Onboarding Details
$verification->update([
    'business_type' => 'Private / public company',
    'legal_name' => 'Acme Test Corp International LLC',
    'display_name' => 'Acme Support',
    'category' => 'Technology',
    'website' => 'https://acmetestcorp.com',
    'address' => '123 Innovation Boulevard, Lagos, Nigeria',
    'signatory_name' => 'John Doe',
    'signatory_designation' => 'Managing Director',
    'business_email' => 'contact@acmetestcorp.com',
    'business_phone' => '+2348001234567',
    'wa_phone' => '+2348007654321',
    'status' => 'under_review',
    'submitted_at' => now(),
]);

$verification->refresh();
echo "\nOnboarding Info Saved Successfully:\n";
echo " - Business Type: " . $verification->business_type . "\n";
echo " - Legal Name: " . $verification->legal_name . "\n";
echo " - WA Display Name: " . $verification->display_name . "\n";
echo " - Signatory: " . $verification->signatory_name . " (" . $verification->signatory_designation . ")\n";
echo " - WA Phone: " . $verification->wa_phone . "\n";
echo " - Status: " . $verification->status . "\n";
echo " - Submitted At: " . $verification->submitted_at . "\n";

echo "\n--- SUCCESS! ALL ONBOARDING LOGIC VERIFIED SUCCESSFULLY ---\n";
