<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AutomationTriggerDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $triggers = [
            // TIME-BASED
            [
                'key' => 'daily_morning_sync',
                'name' => 'Daily Morning Sync',
                'category' => 'time_based',
                'description' => 'Triggers every morning for system-wide sync.',
                'is_system' => true,
                'is_read_only' => true,
                'default_config' => ['repeat_interval' => 'daily', 'start_time' => '08:00'],
                'default_output_variables' => [
                    ['key' => 'trigger_time', 'type' => 'DATETIME'],
                    ['key' => 'sync_id', 'type' => 'STRING'],
                ],
            ],
            [
                'key' => 'weekly_report_export',
                'name' => 'Weekly Report Export',
                'category' => 'time_based',
                'description' => 'Triggers every Monday morning for weekly reporting.',
                'is_system' => true,
                'is_read_only' => true,
                'default_config' => ['repeat_interval' => 'weekly', 'start_time' => '09:00'],
                'default_output_variables' => [
                    ['key' => 'report_date', 'type' => 'DATE'],
                    ['key' => 'week_number', 'type' => 'NUMBER'],
                ],
            ],

            // EVENT-BASED
            [
                'key' => 'new_contact_created',
                'name' => 'New Contact Created',
                'category' => 'event_based',
                'subtype' => 'system_event',
                'description' => 'Fires whenever a new contact is added to the system.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => [
                    ['key' => 'contact.id', 'type' => 'NUMBER'],
                    ['key' => 'contact.name', 'type' => 'STRING'],
                    ['key' => 'contact.phone', 'type' => 'STRING'],
                ],
            ],
            [
                'key' => 'new_message_received',
                'name' => 'New Message Received',
                'category' => 'event_based',
                'subtype' => 'whatsapp_event',
                'description' => 'Fires on every incoming WhatsApp message.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => [
                    ['key' => 'message.id', 'type' => 'STRING'],
                    ['key' => 'message.body', 'type' => 'STRING'],
                    ['key' => 'sender.phone', 'type' => 'STRING'],
                    ['key' => 'sender.name', 'type' => 'STRING'],
                ],
            ],

            // BEHAVIOR-BASED
            [
                'key' => 'inactive_user_24h',
                'name' => 'Inactive User (24h)',
                'category' => 'behavior_based',
                'description' => 'Fires when a customer has not interacted for 24 hours.',
                'is_system' => true,
                'is_read_only' => true,
                'default_config' => ['threshold_hours' => 24],
                'default_output_variables' => [
                    ['key' => 'last_interaction_at', 'type' => 'DATETIME'],
                    ['key' => 'customer.id', 'type' => 'NUMBER'],
                ],
            ],
            [
                'key' => 'cart_abandoned',
                'name' => 'Cart Abandoned',
                'category' => 'behavior_based',
                'description' => 'Fires when a customer leaves items in their cart without checkout.',
                'is_system' => true,
                'is_read_only' => true,
                'default_config' => ['wait_minutes' => 60],
                'default_output_variables' => [
                    ['key' => 'cart.id', 'type' => 'NUMBER'],
                    ['key' => 'cart.total', 'type' => 'NUMBER'],
                ],
            ],

            // WEBHOOK/API
            [
                'key' => 'generic_incoming_webhook',
                'name' => 'Generic Incoming Webhook',
                'category' => 'webhook_api',
                'description' => 'Accepts incoming JSON payloads from external systems.',
                'is_system' => true,
                'is_read_only' => true,
                'default_output_variables' => [
                    ['key' => 'payload', 'type' => 'JSON'],
                ],
            ],

            // CONDITIONAL
            [
                'key' => 'profile_match_trigger',
                'name' => 'Profile Match Trigger',
                'category' => 'conditional',
                'description' => 'Fires when a customer profile matches specific JSON rules.',
                'is_system' => true,
                'is_read_only' => true,
                'default_config' => ['match_mode' => 'all', 'rules' => []],
                'default_output_variables' => [
                    ['key' => 'customer.id', 'type' => 'NUMBER'],
                    ['key' => 'matched_rules_count', 'type' => 'NUMBER'],
                ],
            ],
        ];

        foreach ($triggers as $trigger) {
            \App\Models\AutomationTriggerDefinition::updateOrCreate(
                ['key' => $trigger['key'], 'company_id' => null],
                $trigger
            );
        }
    }
}
