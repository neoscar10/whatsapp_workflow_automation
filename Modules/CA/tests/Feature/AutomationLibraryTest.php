<?php

namespace Modules\CA\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAAutomationLibrary;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CAComplianceRequirement;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Services\AutomationLibraryService;
use Modules\CA\Services\AutomationSuggestionService;
use Modules\CA\Services\AutomationTemplateLibraryService;
use Modules\CA\Services\ReminderRuleService;
use Modules\CA\Services\AutomationConfigurationService;
use App\Models\User;
use App\Models\Company;
use Exception;

class AutomationLibraryTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private CAClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create([
            'name' => 'Test CA Firm',
            'slug' => 'test-ca-firm',
            'primary_email' => 'contact@testca.com',
        ]);
        
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->client = CAClient::create([
            'company_id' => $this->company->id,
            'client_name' => 'Client Company Pvt Ltd',
            'email' => 'client@company.com',
            'phone' => '+919876543210',
            'status' => 'active',
        ]);
    }

    public function test_library_seeding_and_retrieval()
    {
        $service = app(AutomationLibraryService::class);
        $service->seedDefaultLibrary();

        $catalogue = $service->getCatalogue();
        $this->assertGreaterThan(0, $catalogue->count());
        $this->assertNotNull($service->findByFrequency('monthly'));
    }

    public function test_reminder_rule_validation()
    {
        $service = app(ReminderRuleService::class);

        // Valid rules
        $validRules = [
            ['trigger_type' => 'before_due', 'offset_days' => 5, 'send_time' => '09:00', 'is_enabled' => true],
            ['trigger_type' => 'on_due', 'offset_days' => 0, 'send_time' => '09:00', 'is_enabled' => true],
        ];
        
        $service->validate($validRules);
        $this->assertTrue(true); // Should pass without exception

        // Invalid: negative days
        $this->expectException(Exception::class);
        $service->validate([
            ['trigger_type' => 'before_due', 'offset_days' => -1, 'send_time' => '09:00']
        ]);
    }

    public function test_reminder_rule_on_due_validation()
    {
        $service = app(ReminderRuleService::class);

        // Invalid: multiple on_due rules
        $this->expectException(Exception::class);
        $service->validate([
            ['trigger_type' => 'on_due', 'offset_days' => 0, 'send_time' => '09:00'],
            ['trigger_type' => 'on_due', 'offset_days' => 0, 'send_time' => '10:00'],
        ]);
    }

    public function test_automation_configuration_service_saves_correctly()
    {
        $libService = app(AutomationLibraryService::class);
        $libService->seedDefaultLibrary();
        $library = $libService->findByFrequency('monthly');

        $category = CAServiceCategory::create([
            'name' => 'Taxation Services',
            'slug' => 'taxation-services',
        ]);

        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'Monthly GST',
            'slug' => 'monthly-gst',
            'is_recurring' => true,
        ]);
        
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);

        $req = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'GST Invoice Sheets',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => true,
            'recurrence_frequency' => 'monthly',
        ]);

        $configService = app(AutomationConfigurationService::class);

        $configs = [
            [
                'library_id' => $library->id,
                'frequency' => 'monthly',
                'is_enabled' => true,
                'requirement_ids' => [$req->id],
                'rules' => [
                    ['trigger_type' => 'before_due', 'offset_days' => 3, 'send_time' => '09:00', 'is_enabled' => true],
                    ['trigger_type' => 'on_due', 'offset_days' => 0, 'send_time' => '09:00', 'is_enabled' => true],
                ],
                'custom_message_title' => 'Monthly Reminder Title',
                'custom_message_body' => 'Monthly Reminder Body',
            ]
        ];

        $results = $configService->saveConfiguration($this->client, $this->user->id, $configs);

        $this->assertCount(1, $results);
        $this->assertEquals('monthly', $results[0]->frequency);
        
        // Assert rules were saved
        $this->assertDatabaseHas('ca_client_automation_rules', [
            'client_automation_id' => $results[0]->id,
            'trigger_type' => 'before_due',
            'offset_days' => 3,
        ]);
    }

    public function test_can_create_custom_automation_via_livewire()
    {
        $libService = app(AutomationLibraryService::class);
        $libService->seedDefaultLibrary();
        $library = $libService->findByFrequency('monthly');

        \Livewire\Livewire::actingAs($this->user)
            ->test(\Modules\CA\Livewire\AutomationLibraryPage::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->set('selectedLibraryId', $library->id)
            ->assertSet('customName', $library->name)
            ->set('customName', 'Custom Super Tax Reminder')
            ->set('templateTitle', 'Reminder: Please Upload')
            ->set('templateBody', 'Hello client, please upload {{document_name}}.')
            ->set('editingRules', [
                ['trigger_type' => 'before_due', 'offset_days' => 2, 'send_time' => '10:00', 'is_enabled' => true],
            ])
            ->call('createCustomAutomation')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('ca_client_automations', [
            'company_id' => $this->company->id,
            'client_id' => null,
            'automation_library_id' => $library->id,
            'frequency' => 'monthly',
        ]);

        $automation = CAClientAutomation::where('company_id', $this->company->id)->first();
        $this->assertNotNull($automation);
        $this->assertEquals('Custom Super Tax Reminder', $automation->metadata_json['custom_name']);
        $this->assertEquals('Reminder: Please Upload', $automation->metadata_json['custom_message_title']);

        $this->assertDatabaseHas('ca_client_automation_rules', [
            'client_automation_id' => $automation->id,
            'trigger_type' => 'before_due',
            'offset_days' => 2,
            'send_time' => '10:00',
        ]);
    }
}
