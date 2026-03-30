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
        if ($node->type !== 'trigger') {
            return null;
        }

        // Validate conditions if any
        if (!$this->validateTriggerConditions($node, $payload)) {
            Log::info("Automation [{$node->automation_flow_id}] trigger conditions not met.");
            return null;
        }

        $run = AutomationRun::create([
            'automation_flow_id' => $node->automation_flow_id,
            'company_id' => $node->flow->company_id,
            'status' => 'running',
            'trigger_node_id' => $node->id,
            'trigger_context' => $payload,
            'started_at' => now(),
        ]);

        Log::info("Automation Run [{$run->id}] started for flow [{$node->automation_flow_id}]");

        // In a real implementation, we would dispatch a job to process the next nodes.
        // For now, mirroring the execution engine's handoff.
        $this->dispatchRun($run);

        return $run;
    }

    /**
     * Validate JSON rules for the trigger if present.
     */
    protected function validateTriggerConditions(AutomationNode $node, array $payload): bool
    {
        $config = $node->config ?? [];
        $rules = $config['rules'] ?? $config['conditions']['rules'] ?? [];
        $mode = $config['match_mode'] ?? $config['conditions']['match_mode'] ?? 'all';

        if (empty($rules)) {
            return true;
        }

        return app(AutomationRuleEvaluator::class)->evaluate($rules, $payload, $mode);
    }

    /**
     * Hand off the run to the background execution engine.
     */
    protected function dispatchRun(AutomationRun $run)
    {
        // Placeholder for the actual background worker dispatch
        // e.g., ProcessAutomationRun::dispatch($run);
    }
}
