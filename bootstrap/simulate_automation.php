<?php

use App\Models\User;
use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAAutomationLibrary;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CAReminderActivity;
use Modules\CA\Models\CAClientAutomationRule;
use Modules\CA\Jobs\MonitorComplianceDeadlines;
use Modules\CA\Jobs\EvaluateAutomationReminders;
use Illuminate\Support\Carbon;

echo "==================================================\n";
echo "🚀 LOCAL WHATSAPP AUTOMATION END-TO-END SIMULATOR\n";
echo "==================================================\n\n";

// 1. Identify Target Local Developer (User 4)
$user = User::find(4);
if (!$user) {
    echo "❌ Local User with ID 4 not found. Please run seeders first.\n";
    exit(1);
}

$company = $user->company;
echo "🏢 Company: {$company->name} (ID: {$company->id})\n";

// 2. Setup WABA Simulated Account
$simulatorService = app(\App\Services\WhatsApp\Simulation\WhatsAppInboundSimulatorService::class);
// This guarantees local fake WABA phone number + fake account exist
$localNumber = $simulatorService->ensureFakePhoneNumber($company->id, $user->id);
echo "📞 Fake WABA Number Ready: {$localNumber->phone_number} (WABA ID: {$localNumber->account->waba_id})\n";

// 3. Create or Find CA Client & Matching Contact
$phone = '919876543210';
$normalizedPhone = \App\Support\PhoneNumberNormalizer::normalize($phone);

$contact = \App\Models\Contact\Contact::firstOrCreate(
    ['company_id' => $company->id, 'normalized_phone' => $normalizedPhone],
    [
        'name' => 'Demo Local Client',
        'phone' => $phone,
        'source' => 'ca_onboarding',
        'status' => 'active',
        'created_by_user_id' => $user->id,
    ]
);
echo "👤 Contact record: {$contact->name} (ID: {$contact->id})\n";

$client = CAClient::firstOrCreate(
    ['company_id' => $company->id, 'phone' => $phone],
    [
        'client_name' => 'Demo Local Client',
        'email' => 'demo_local@example.com',
        'status' => 'active',
        'contact_id' => $contact->id,
    ]
);
if (!$client->contact_id) {
    $client->update(['contact_id' => $contact->id]);
}
echo "👤 CA Client: {$client->client_name} (Phone: {$client->phone})\n";

// 4. Create Compliance and Requirement due today
$category = CAServiceCategory::firstOrCreate(
    ['slug' => 'gst-tax'],
    ['name' => 'GST Tax Service']
);

$compliance = CACompliance::firstOrCreate(
    ['slug' => 'local-gst-return'],
    [
        'ca_service_category_id' => $category->id,
        'name' => 'Local GST Return Compliance',
        'is_recurring' => true,
    ]
);

$clientCompliance = CAClientCompliance::firstOrCreate(
    ['ca_client_id' => $client->id, 'ca_compliance_id' => $compliance->id],
    ['status' => 'active']
);

// Find or create exactly ONE pending requirement (avoids duplicates on re-runs)
$requirement = CAClientComplianceRequirement::firstOrCreate(
    [
        'ca_client_compliance_id' => $clientCompliance->id,
        'name'                   => 'GST Sales Invoices',
    ],
    [
        'requirement_type' => 'document',
        'input_type'       => 'file',
        'is_recurring'     => true,
        'next_due_date'    => Carbon::now()->toDateString(),
        'status'           => 'pending',
    ]
);
// Reset to pending & update due date if it was previously fulfilled
if (!in_array($requirement->status, ['pending', 'under_review'])) {
    $requirement->update(['status' => 'pending', 'next_due_date' => Carbon::now()->toDateString()]);
}
echo "📋 Compliance Requirement: {$requirement->name} (Due: {$requirement->next_due_date}, Status: {$requirement->status})\n";

// 5. Create active Automation with rule for today
$library = CAAutomationLibrary::firstOrCreate(
    ['slug' => 'gst-sales-invoices-reminder'],
    [
        'name' => 'GST Sales Invoices Reminder',
        'frequency' => 'monthly',
        'status' => 'active',
    ]
);

$automation = CAClientAutomation::firstOrCreate(
    [
        'company_id'            => $company->id,
        'client_id'             => $client->id,
        'automation_library_id' => $library->id,
    ],
    [
        'frequency'  => 'monthly',
        'status'     => 'active',
        'is_enabled' => true,
        'created_by' => $user->id,
    ]
);
// Ensure always active
$automation->update(['is_enabled' => true, 'status' => 'active']);

// Map requirement to automation (only if not already mapped)
if (!$automation->documentMappings()->where('ca_client_compliance_requirement_id', $requirement->id)->exists()) {
    $automation->documentMappings()->create([
        'ca_client_compliance_requirement_id' => $requirement->id,
    ]);
}

// Find or create rule (update send_time to now on each run so it always triggers)
$rule = CAClientAutomationRule::firstOrCreate(
    [
        'client_automation_id' => $automation->id,
        'trigger_type'         => 'on_due',
        'offset_days'          => 0,
    ],
    [
        'send_time'  => Carbon::now()->format('H:i'),
        'is_enabled' => true,
    ]
);
$rule->update(['send_time' => Carbon::now()->format('H:i'), 'is_enabled' => true]);
echo "🤖 Automation Enabled with Rule Trigger Time: {$rule->send_time}\n";

// 6. Pre-flight Template Setup
$templateManagementService = app(\Modules\CA\Services\TemplateManagementService::class);
$template = $templateManagementService->resolveTemplateForAutomation($automation);
if ($template) {
    echo "📄 Template Prepared: {$template->remote_template_name} (Status: {$template->status})\n";
}

// 7. Run monitoring and scheduler jobs synchronously
echo "⚙️ Running Compliance Deadline Monitor...\n";
$monitoringJob = new MonitorComplianceDeadlines();
$monitoringJob->handle(app(\Modules\CA\Services\DeadlineMonitoringService::class));

echo "⚙️ Running Reminder Scheduler & Evaluator...\n";
$schedulerJob = new EvaluateAutomationReminders();
$schedulerJob->handle();

// Check if reminder was scheduled and sent
$activity = CAReminderActivity::where('ca_client_automation_id', $automation->id)
    ->where('ca_client_compliance_requirement_id', $requirement->id)
    ->first();

if ($activity) {
    echo "\n🎉 Success! Reminder Activity Logged: ID #{$activity->id} (Status: {$activity->status})\n";
    
    if ($activity->status === 'scheduled') {
        echo "⚙️ Executing reminder job synchronously...\n";
        app(\Modules\CA\Services\AutomationExecutionService::class)->executeReminder($activity->id);
        $activity->refresh();
        echo "🔄 Updated Activity Status: {$activity->status}\n";
    }

    if ($activity->status === 'sent') {
        $message = \App\Models\Chat\ConversationMessage::where('message_type', 'template')
            ->orderByDesc('id')
            ->first();
        if ($message) {
            echo "💬 Simulated WhatsApp Template Message Outbound: '{$message->body}'\n";
            echo "🆔 Simulated External Message ID: {$message->external_message_id}\n";
            $wallet = app(\App\Services\Wallet\WalletService::class)->getOrCreateWallet($company->owner);
            echo "💰 Wallet Balance After Debit: " . $wallet->balance . "\n";
        }
    } else {
        echo "❌ Reminder execution failed. Error message: {$activity->error_message}\n";
    }
} else {
    echo "\n❌ No reminder activity was triggered. Please verify date & time triggers.\n";
}
echo "==================================================\n";
