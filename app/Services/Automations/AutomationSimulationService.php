<?php

namespace App\Services\Automations;

use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\SimulationSession;
use App\Models\SimulationStep;
use Illuminate\Support\Facades\Log;

class AutomationSimulationService
{
    /**
     * Start a simulation session.
     */
    public function start(SimulationSession $session)
    {
        if ($session->status !== 'ready') {
            return;
        }

        // Find the trigger node
        $trigger = AutomationNode::where('automation_flow_id', $session->automation_flow_id)
            ->where('type', 'trigger')
            ->first();

        if (!$trigger) {
            $this->failSession($session, 'No trigger node found for this workflow.');
            return;
        }

        $session->update([
            'status' => 'running',
            'started_at' => now(),
            'current_node_id' => $trigger->id,
            'context' => $session->initial_payload ?? [],
        ]);
    }

    /**
     * Execute the current node in the session and advance to the next.
     */
    public function executeNextStep(SimulationSession $session)
    {
        if ($session->status !== 'running' || !$session->current_node_id) {
            return;
        }

        $node = AutomationNode::find($session->current_node_id);
        if (!$node) {
            $this->failSession($session, "Node [{$session->current_node_id}] not found.");
            return;
        }

        $context = $session->context ?? [];

        // Record step start
        $step = SimulationStep::create([
            'simulation_session_id' => $session->id,
            'node_id' => $node->id,
            'node_type' => $node->type,
            'node_subtype' => $node->subtype,
            'status' => 'running',
            'input_snapshot' => $context,
            'order_index' => $session->steps()->count() + 1,
        ]);

        try {
            $result = $this->handleNodeSimulation($session, $node, $context);
            
            $step->update([
                'status' => 'success',
                'output_snapshot' => $result['output'] ?? [],
                'log_message' => $result['message'] ?? 'Node executed successfully.',
            ]);

            $newContext = array_merge($context, $result['output'] ?? []);
            
            $session->update([
                'context' => $newContext,
                'current_node_id' => $result['next_node_id'] ?? null,
            ]);

            if (!isset($result['next_node_id']) || !$result['next_node_id']) {
                $this->completeSession($session);
            }
        } catch (\Exception $e) {
            $step->update([
                'status' => 'failed',
                'log_message' => $e->getMessage(),
            ]);
            $this->failSession($session, $e->getMessage());
        }
    }

    /**
     * Route simulation to specific handlers.
     */
    protected function handleNodeSimulation(SimulationSession $session, AutomationNode $node, array $context): array
    {
        return match($node->type) {
            'trigger' => $this->simulateTrigger($node, $context),
            'action' => $this->simulateAction($node, $context),
            'condition' => $this->simulateCondition($node, $context),
            'wait' => $this->simulateWait($node, $context),
            'loop' => $this->simulateLoop($session, $node, $context),
            default => [
                'message' => 'Node type not supported in simulation yet.',
                'output' => [],
            ]
        };
    }

    protected function simulateTrigger(AutomationNode $node, array $context): array
    {
        return [
            'message' => 'Trigger activated with test payload.',
            'output' => $context, // Trigger output is the initial payload
            'next_node_id' => $this->getNextNodeId($node),
        ];
    }

    protected function simulateAction(AutomationNode $node, array $context): array
    {
        $messageBody = $node->config['message_body'] ?? '';
        $resolvedMessage = $this->resolveVariables($messageBody, $context);
        
        $logMessage = "Action [" . ($node->config['label'] ?? $node->subtype) . "] prepared payload.";
        
        return [
            'message' => $logMessage,
            'output' => [
                'simulated_payload' => $resolvedMessage,
                'recipient' => $this->resolveVariables($node->config['recipient_expression'] ?? '', $context),
                'status' => 'simulated_success',
            ],
            'next_node_id' => $this->getNextNodeId($node),
        ];
    }

    protected function simulateCondition(AutomationNode $node, array $context): array
    {
        $rules = $node->config['rules'] ?? [];
        $matchMode = $node->config['match_mode'] ?? 'and';
        
        // Handle rule groups if present (legacy or complex)
        if (isset($node->config['rule_groups'])) {
            $matched = $this->evaluateRuleGroups($node->config['rule_groups'], $context);
        } else {
            $matched = $this->evaluateRules($rules, $context, $matchMode);
        }

        return [
            'message' => 'Condition evaluated to ' . ($matched ? 'TRUE' : 'FALSE') . ' (Simulated).',
            'output' => ['match' => $matched],
            'next_node_id' => $this->getNextNodeId($node, $matched ? 'true' : 'false'),
        ];
    }

    protected function evaluateRules(array $rules, array $context, string $mode = 'all'): bool
    {
        return app(AutomationRuleEvaluator::class)->evaluate($rules, $context, $mode);
    }

    protected function evaluateRuleGroups(array $groups, array $context): bool
    {
        return app(AutomationRuleEvaluator::class)->evaluateGroups($groups, $context);
    }

    protected function getValueFromContext(string $field, array $context)
    {
        return app(AutomationRuleEvaluator::class)->getValueFromContext($field, $context);
    }

    protected function compareValues($actual, $operator, $expected): bool
    {
        return app(AutomationRuleEvaluator::class)->compareValues($actual, $operator, $expected);
    }

    protected function resolveVariables(string $text, array $context): string
    {
        return preg_replace_callback('/\{\{([^\}]+)\}\}/', function($matches) use ($context) {
            $field = trim($matches[1]);
            // Remove 'trigger.' prefix if present for lookup
            if (str_starts_with($field, 'trigger.')) {
                $field = substr($field, 8);
            }
            return $this->getValueFromContext($field, $context) ?? $matches[0];
        }, $text);
    }

    protected function simulateLoop(SimulationSession $session, AutomationNode $node, array $context): array
    {
        $iterationCount = ($session->meta['loop_iterations'][$node->id] ?? 0) + 1;
        $maxLimit = $node->config['max_iteration_limit'] ?? 100;

        // Update iteration count in session meta
        $meta = $session->meta ?? [];
        $meta['loop_iterations'][$node->id] = $iterationCount;
        $session->update(['meta' => $meta]);

        if ($iterationCount > $maxLimit) {
            return [
                'message' => "Loop iteration limit ($maxLimit) exceeded. Exiting loop.",
                'output' => ['loop_error' => 'MAX_ITERATIONS_EXCEEDED'],
                'next_node_id' => $this->getNextNodeId($node, 'exit'),
            ];
        }

        $shouldContinue = true;
        if ($node->subtype === 'condition_based') {
            $shouldContinue = $this->evaluateRules($node->config['condition']['rules'] ?? [], $context);
        }

        if ($shouldContinue) {
            return [
                'message' => "Loop iteration $iterationCount started.",
                'output' => ['iteration' => $iterationCount],
                'next_node_id' => $this->getNextNodeId($node, 'body'),
            ];
        } else {
            return [
                'message' => "Loop condition no longer met. Exiting.",
                'output' => ['loop_completed' => true],
                'next_node_id' => $this->getNextNodeId($node, 'exit'),
            ];
        }
    }

    protected function simulateWait(AutomationNode $node, array $context): array
    {
        return [
            'message' => 'Wait period simulated. Moving to next step.',
            'output' => [],
            'next_node_id' => $this->getNextNodeId($node),
        ];
    }

    protected function getNextNodeId(AutomationNode $node, string $outcome = null): ?int
    {
        $query = $node->outgoingConnections();
        if ($outcome) {
            $query->where('source_handle', $outcome);
        }
        $connection = $query->first();
        return $connection ? $connection->target_node_id : null;
    }

    protected function completeSession(SimulationSession $session)
    {
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'current_node_id' => null,
        ]);
    }

    protected function failSession(SimulationSession $session, string $error)
    {
        $session->update([
            'status' => 'failed',
            'completed_at' => now(),
            'meta' => array_merge($session->meta ?? [], ['error' => $error]),
        ]);
    }
}
