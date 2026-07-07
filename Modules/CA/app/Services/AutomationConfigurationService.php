<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientAutomationDocument;
use Modules\CA\Events\ClientAutomationCreated;
use Modules\CA\Events\AutomationConfigurationCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AutomationConfigurationService
{
    public function __construct(
        private ReminderRuleService $reminderRuleService,
        private TemplateManagementService $templateManagementService
    ) {}

    /**
     * Persist automation configuration for a client.
     *
     * Each entry in $automationConfigs should look like:
     * [
     *   'library_id'   => int,
     *   'frequency'    => string,
     *   'is_enabled'   => bool,
     *   'requirement_ids' => int[],   // ca_client_compliance_requirement IDs
     *   'rules'        => [...],      // reminder rule arrays
     *   'custom_message_title' => string|null,
     *   'custom_message_body'  => string|null,
     * ]
     *
     * @param CAClient $client
     * @param int $createdBy
     * @param array $automationConfigs
     * @return CAClientAutomation[]
     */
    public function saveConfiguration(CAClient $client, int $createdBy, array $automationConfigs): array
    {
        $savedAutomations = [];

        DB::transaction(function () use ($client, $createdBy, $automationConfigs, &$savedAutomations) {
            foreach ($automationConfigs as $config) {
                $libraryId = $config['library_id'] ?? null;
                if (!$libraryId) continue;

                // Upsert: if automation already exists for this client+library, update it
                $automation = CAClientAutomation::updateOrCreate(
                    [
                        'company_id'            => $client->company_id,
                        'client_id'             => $client->id,
                        'automation_library_id' => $libraryId,
                    ],
                    [
                        'frequency'  => $config['frequency'],
                        'status'     => 'active',
                        'is_enabled' => (bool) ($config['is_enabled'] ?? true),
                        'created_by' => $createdBy,
                        'metadata_json' => array_filter([
                            'custom_message_title' => $config['custom_message_title'] ?? null,
                            'custom_message_body'  => $config['custom_message_body'] ?? null,
                        ]),
                    ]
                );

                // Dispatch creation event if newly created
                if ($automation->wasRecentlyCreated) {
                    ClientAutomationCreated::dispatch($automation);
                }

                // Sync document mappings (clear and re-attach)
                CAClientAutomationDocument::where('client_automation_id', $automation->id)->delete();
                foreach (($config['requirement_ids'] ?? []) as $reqId) {
                    CAClientAutomationDocument::create([
                        'client_automation_id'               => $automation->id,
                        'ca_client_compliance_requirement_id' => $reqId,
                    ]);
                }

                // Save reminder rules
                if (!empty($config['rules'])) {
                    $this->reminderRuleService->saveRules($automation->id, $config['rules']);
                }

                // Resolve and Provision WABA Template
                try {
                    $this->templateManagementService->resolveTemplateForAutomation($automation);
                } catch (Exception $e) {
                    Log::warning("WABA template resolution failed for automation ID {$automation->id}: " . $e->getMessage());
                }

                $savedAutomations[] = $automation;
            }
        });

        // Dispatch the completed event
        if (!empty($savedAutomations)) {
            AutomationConfigurationCompleted::dispatch(
                $client,
                array_map(fn($a) => $a->id, $savedAutomations)
            );
        }

        return $savedAutomations;
    }

    /**
     * Delete all automation configuration for a client (e.g. when onboarding is reset).
     */
    public function clearForClient(int $clientId): void
    {
        $automations = CAClientAutomation::where('client_id', $clientId)->get();
        foreach ($automations as $automation) {
            $automation->rules()->delete();
            $automation->documentMappings()->delete();
            $automation->delete();
        }
    }
}
