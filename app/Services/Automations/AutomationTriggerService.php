<?php

namespace App\Services\Automations;

use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\AutomationRun;
use App\Models\AutomationTriggerDefinition;
use Illuminate\Support\Facades\Log;

class AutomationTriggerService
{
    /**
     * Get variables exposed by a specific trigger node.
     */
    public function getExposedVariables(AutomationNode $node): array
    {
        if ($node->type !== 'trigger') {
            return [];
        }

        $config = $node->config ?? [];
        
        // 1. Check for detected variables (Webhooks)
        if (!empty($config['detected_variables'])) {
            return $config['detected_variables'];
        }

        // 2. Check for manual/default variables in node config
        if (!empty($config['output_variables'])) {
            return $config['output_variables'];
        }

        // 3. Fallback to definition defaults
        $definitionKey = $config['trigger_definition_key'] ?? $node->subtype;
        $definition = AutomationTriggerDefinition::where('key', $definitionKey)
            ->where(fn($q) => $q->whereNull('company_id')->orWhere('company_id', $node->flow->company_id))
            ->first();

        return $definition ? ($definition->default_output_variables ?? []) : [];
    }

    /**
     * Start an automation run from a trigger hit.
     */
    public function fireTrigger(AutomationNode $node, array $payload = []): ?AutomationRun
    {
        \Illuminate\Support\Facades\Log::info("Entered fireTrigger for Node [{$node->id}] Flow [{$node->automation_flow_id}]", [
            'company_id' => $node->flow->company_id,
            'node_type' => $node->type
        ]);

        if ($node->type !== 'trigger') {
            \Illuminate\Support\Facades\Log::info("Aborting Trigger: Node [{$node->id}] is not a trigger type.");
            return null;
        }

        // Validate conditions if any
        if (!$this->validateTriggerConditions($node, $payload)) {
            \Illuminate\Support\Facades\Log::info("Automation [{$node->automation_flow_id}] trigger conditions not met.");
            return null;
        }

        try {
            $runData = [
                'automation_flow_id' => $node->automation_flow_id,
                'company_id' => $node->flow->company_id,
                'status' => 'running',
                'trigger_node_id' => $node->id,
                'trigger_context' => $payload,
                'started_at' => now(),
            ];

            \Illuminate\Support\Facades\Log::info("About to create AutomationRun", ['payload' => $runData]);

            $run = AutomationRun::create($runData);

            \Illuminate\Support\Facades\Log::info("AutomationRun created successfully", [
                'run_id' => $run->id,
                'status' => $run->status
            ]);

            // In a real implementation, we would dispatch a job to process the next nodes.
            // For now, mirroring the execution engine's handoff.
            $this->dispatchRun($run);

            return $run;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("CRITICAL FAILURE during AutomationRun creation", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'flow_id' => $node->automation_flow_id
            ]);
            return null;
        }
    }

    /**
     * Validate JSON rules for the trigger if present.
     */
    protected function validateTriggerConditions(AutomationNode $node, array $payload): bool
    {
        $config = $node->config ?? [];
        $rules = $config['rules'] ?? $config['rule_groups'] ?? [];
        $mode = $config['match_mode'] ?? 'all';

        if (empty($rules)) {
            return true;
        }

        $evaluator = app(AutomationRuleEvaluator::class);
        $result = [];

        if (isset($config['rule_groups'])) {
            $result = $evaluator->evaluateGroupsDetailed($config['rule_groups'], $payload);
        } else {
            $result = $evaluator->evaluateDetailed($rules, $payload, $mode);
        }

        if (!$result['match']) {
            \Illuminate\Support\Facades\Log::info("Automation [{$node->automation_flow_id}] Trigger [{$node->id}] Condition FAILED.", [
                'summary' => $result['summary'],
                'details' => $result['groups'] ?? $result['rules'] ?? []
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info("Automation [{$node->automation_flow_id}] Trigger [{$node->id}] Condition PASSED.");
        }

        return $result['match'];
    }

    public function __construct(
        protected AutomationRunnerService $runner
    ) {}

    /**
     * Hand off the run to the background execution engine.
     */
    protected function dispatchRun(AutomationRun $run)
    {
        // For now, execute synchronously to ensure immediate feedback in this task
        // In a high-scale environment, this would be: ProcessAutomationRun::dispatch($run);
        $this->runner->executeRun($run);
    }
}
