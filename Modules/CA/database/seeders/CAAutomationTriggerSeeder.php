<?php

namespace Modules\CA\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutomationTriggerDefinition;

class CAAutomationTriggerSeeder extends Seeder
{
    public function run(): void
    {
        $defaultVariables = [
            ['key' => 'client_name', 'type' => 'STRING'],
            ['key' => 'client_phone', 'type' => 'STRING'],
            ['key' => 'business_type', 'type' => 'STRING'],
            ['key' => 'compliance_name', 'type' => 'STRING'],
            ['key' => 'deadline_date', 'type' => 'DATE'],
            ['key' => 'days_remaining', 'type' => 'NUMBER'],
            ['key' => 'document_name', 'type' => 'STRING'],
            ['key' => 'document_status', 'type' => 'STRING'],
            ['key' => 'firm_name', 'type' => 'STRING'],
        ];

        $triggers = [
            // Compliance & Deadlines
            [
                'key' => 'ca.compliance_due',
                'name' => 'Compliance Due Soon',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a compliance deadline is approaching.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.compliance_overdue',
                'name' => 'Compliance Overdue',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a compliance deadline has passed without completion.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.compliance_completed',
                'name' => 'Compliance Completed',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when all requirements for a compliance are fulfilled.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],

            // Document Workflow
            [
                'key' => 'ca.document_missing',
                'name' => 'Document Missing',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires to request a missing document.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.document_uploaded',
                'name' => 'Document Uploaded',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a client uploads a document.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.document_approved',
                'name' => 'Document Approved',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a document is approved by staff.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.document_rejected',
                'name' => 'Document Rejected',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a document is rejected and needs re-upload.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.document_expiring',
                'name' => 'Document Expiring',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when an approved document is nearing its expiry date.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],

            // Requirements
            [
                'key' => 'ca.requirement_completed',
                'name' => 'Requirement Completed',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a specific compliance requirement is met.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
            [
                'key' => 'ca.requirement_overdue',
                'name' => 'Requirement Overdue',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a specific compliance requirement deadline has passed.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],

            // Client Lifecycle
            [
                'key' => 'ca.client_onboarded',
                'name' => 'Client Onboarded',
                'category' => 'event_based',
                'subtype' => 'ca_event',
                'description' => 'Fires when a new CA client is completely onboarded.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => $defaultVariables,
            ],
        ];

        foreach ($triggers as $trigger) {
            AutomationTriggerDefinition::updateOrCreate(
                ['key' => $trigger['key'], 'company_id' => null],
                $trigger
            );
        }
    }
}
