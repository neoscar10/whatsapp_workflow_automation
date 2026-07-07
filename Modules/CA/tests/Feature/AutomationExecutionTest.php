<?php

namespace Modules\CA\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\Chat\Conversation;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAAutomationLibrary;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CAReminderActivity;
use Modules\CA\Models\CAClientAutomationRule;
use Modules\CA\Services\TemplateManagementService;
use Modules\CA\Services\VariableResolverService;
use Modules\CA\Services\ReminderSchedulerService;
use Modules\CA\Services\AutomationExecutionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Modules\CA\Jobs\DispatchAutomationReminder;
use Exception;

class AutomationExecutionTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private CAClient $client;
    private WhatsAppAccount $wabaAccount;
    private WhatsAppPhoneNumber $wabaPhone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'CA Firm Pvt Ltd',
            'slug' => 'ca-firm-pvt-ltd',
            'primary_email' => 'firm@ca.com',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->client = CAClient::create([
            'company_id' => $this->company->id,
            'client_name' => 'BigClient Corp',
            'email' => 'contact@bigclient.com',
            'phone' => '919876543210',
            'status' => 'active',
        ]);

        // Connect WABA credentials for testing
        $this->wabaAccount = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'mock_token',
            'waba_id' => '1234567890',
            'connection_status' => 'connected',
        ]);

        $this->wabaPhone = WhatsAppPhoneNumber::create([
            'whatsapp_account_id' => $this->wabaAccount->id,
            'company_id' => $this->company->id,
            'phone_number_id' => '987654',
            'phone_number' => '918888888888',
            'verified_name' => 'CA Firm Outbound',
            'display_name' => 'CA Firm Outbound',
            'status' => 'verified',
        ]);
    }

    public function test_variable_resolution_to_meta_parameters()
    {
        // Set fixed test time to midnight to avoid clock drift and partial day truncation
        Carbon::setTestNow('2026-07-01 00:00:00');

        $category = CAServiceCategory::create(['name' => 'Tax', 'slug' => 'tax']);
        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'GST return',
            'slug' => 'gst-return',
            'is_recurring' => true,
        ]);
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);
        $requirement = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'Monthly Bank Statement',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => true,
            'next_due_date' => '2026-07-06',
        ]);

        $resolver = app(VariableResolverService::class);
        $resolved = $resolver->resolveVariables($requirement, $this->client, $this->company);

        $this->assertEquals('BigClient Corp', $resolved['client_name']);
        $this->assertEquals('Monthly Bank Statement', $resolved['document_name']);
        $this->assertEquals('5', $resolved['days_remaining']);

        // Clean up test now
        Carbon::setTestNow();

        // Test parameters mapping format
        $metaParams = $resolver->resolveMetaComponents($resolved);
        $this->assertEquals('body', $metaParams[0]['type']);
        $this->assertEquals('BigClient Corp', $metaParams[0]['parameters'][0]['text']);
        $this->assertEquals('CA Firm Pvt Ltd', $metaParams[0]['parameters'][1]['text']);
    }

    public function test_duplicate_template_prevention_reuses_approved_templates()
    {
        // Pre-create an approved template in DB representing Monthly Document Collection
        $approvedTemplate = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->wabaAccount->id,
            'remote_template_name' => 'ca_monthly_document_collection',
            'display_title' => 'CA Monthly Document Collection',
            'category' => 'utility',
            'language_code' => 'en_us',
            'status' => 'approved',
            'body_text' => 'Hello {{1}}, from {{2}}',
        ]);

        $library = CAAutomationLibrary::create([
            'name' => 'Monthly Document Collection',
            'slug' => 'monthly-document-collection',
            'frequency' => 'monthly',
            'status' => 'active',
        ]);

        $automation = CAClientAutomation::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'automation_library_id' => $library->id,
            'frequency' => 'monthly',
            'status' => 'active',
            'is_enabled' => true,
            'created_by' => $this->user->id,
        ]);

        $manager = app(TemplateManagementService::class);
        $resolved = $manager->resolveTemplateForAutomation($automation);

        // Assert it reuses the existing approved template and doesn't trigger mock API creation
        $this->assertEquals($approvedTemplate->id, $resolved->id);
        $this->assertEquals($approvedTemplate->id, $automation->fresh()->whatsapp_template_id);
    }

    public function test_scheduler_queues_pending_reminders()
    {
        Queue::fake();

        $library = CAAutomationLibrary::create([
            'name' => 'Monthly Collection',
            'slug' => 'monthly-collection',
            'frequency' => 'monthly',
            'status' => 'active',
        ]);

        $automation = CAClientAutomation::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'automation_library_id' => $library->id,
            'frequency' => 'monthly',
            'status' => 'active',
            'is_enabled' => true,
            'created_by' => $this->user->id,
        ]);

        // Trigger on_due rule
        $rule = CAClientAutomationRule::create([
            'client_automation_id' => $automation->id,
            'trigger_type' => 'on_due',
            'offset_days' => 0,
            'send_time' => Carbon::now()->format('H:i'),
        ]);

        $category = CAServiceCategory::create(['name' => 'Tax', 'slug' => 'tax']);
        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'GST return',
            'slug' => 'gst-return',
            'is_recurring' => true,
        ]);
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);

        // Create requirement due today/now
        $requirement = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'Monthly Bank Statement',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => true,
            'next_due_date' => Carbon::now()->toDateString(),
        ]);

        // Map requirement to automation
        $automation->documentMappings()->create([
            'ca_client_compliance_requirement_id' => $requirement->id,
        ]);

        $scheduler = app(ReminderSchedulerService::class);
        $count = $scheduler->evaluateAndScheduleReminders();

        $this->assertEquals(1, $count);
        Queue::assertPushed(DispatchAutomationReminder::class);
    }

    public function test_execution_blocks_non_approved_templates()
    {
        $library = CAAutomationLibrary::create([
            'name' => 'Monthly Collection',
            'slug' => 'monthly-collection',
            'frequency' => 'monthly',
            'status' => 'active',
        ]);

        $pendingTemplate = WhatsAppTemplate::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->wabaAccount->id,
            'remote_template_name' => 'ca_monthly_collection',
            'display_title' => 'CA Monthly Collection',
            'category' => 'utility',
            'language_code' => 'en_us',
            'status' => 'pending', // NOT approved/active
            'body_text' => 'Monthly compliance reminder: {{1}}',
        ]);

        $automation = CAClientAutomation::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'automation_library_id' => $library->id,
            'frequency' => 'monthly',
            'status' => 'active',
            'is_enabled' => true,
            'created_by' => $this->user->id,
            'whatsapp_template_id' => $pendingTemplate->id,
        ]);

        $category = CAServiceCategory::create(['name' => 'Tax', 'slug' => 'tax']);
        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'GST return',
            'slug' => 'gst-return',
            'is_recurring' => true,
        ]);
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);

        $requirement = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'Monthly Bank Statement',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => true,
            'next_due_date' => Carbon::now()->toDateString(),
        ]);

        $activity = CAReminderActivity::create([
            'company_id' => $this->company->id,
            'ca_client_automation_id' => $automation->id,
            'ca_client_compliance_requirement_id' => $requirement->id,
            'status' => 'scheduled',
        ]);

        $executor = app(AutomationExecutionService::class);
        $result = $executor->executeReminder($activity->id);

        // Assert sending is blocked/fails due to non-approved template
        $this->assertFalse($result);
        $this->assertEquals('failed', $activity->fresh()->status);
        $this->assertStringContainsString('is in status \'pending\'', $activity->fresh()->error_message);
    }
}
