<?php

namespace App\Services\Automations;

use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\AutomationRun;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use App\Jobs\ProcessAutomationNode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutomationRunnerService
{
    public function __construct(
        protected AutomationSimulationService $simulationService,
        protected AutomationRuleEvaluator $ruleEvaluator,
        protected WhatsAppOutboundMessageService $whatsappService
    ) {}

    /**
     * Entry point to start an automation run.
     * Prepares state and dispatches the first node job.
     */
    public function executeRun(AutomationRun $run): void
    {
        $run->update([
            'status' => 'running',
            'started_at' => now(),
            'context' => $run->trigger_context ?? [],
            'step_count' => 0,
            'current_node_id' => $run->trigger_node_id,
        ]);

        \Illuminate\Support\Facades\Log::info("Automation Run [{$run->id}] initialized and starting. Context snapshot created.", [
            'flow_id' => $run->automation_flow_id,
            'node_id' => $run->trigger_node_id
        ]);

        ProcessAutomationNode::dispatch($run, $run->trigger_node_id);
    }

    /**
     * Process a single node in the automation chain.
     * This is typically called by the ProcessAutomationNode Job.
     */
    public function processNodeStep(AutomationRun $run, int $nodeId): void
    {
        $node = AutomationNode::find($nodeId);
        if (!$node) {
            $this->failRun($run, "Node [{$nodeId}] not found.");
            return;
        }

        // Loop Protection
        $maxSteps = 50;
        if ($run->step_count >= $maxSteps) {
            $this->failRun($run, "Maximum step execution limit ($maxSteps) exceeded. Possible loop detected.");
            return;
        }

        $context = $run->context ?? [];
        
        try {
            // Execute the node's business logic
            $result = $this->executeNode($node, $context, $run);
            $outcome = is_array($result) ? ($result['outcome'] ?? 'success') : $result;
            $output = is_array($result) ? ($result['output'] ?? []) : [];

            // Merge output into context for downstream nodes
            $newContext = array_merge($context, $output);
            
            // Log node execution in metadata
            $metadata = $run->metadata ?? [];
            $metadata['execution_log'][] = [
                'node_id' => $node->id,
                'subtype' => $node->subtype,
                'outcome' => $outcome,
                'timestamp' => now()->toDateTimeString()
            ];

            $run->update([
                'context' => $newContext,
                'step_count' => $run->step_count + 1,
                'metadata' => $metadata
            ]);

            // Determine next node
            $nextNodeId = $this->simulationService->getNextNodeId($node, $outcome, $newContext);
            
            if (!$nextNodeId) {
                $this->completeRun($run);
                return;
            }

            // Handle Timing (Wait nodes)
            if ($node->type === 'wait') {
                $this->scheduleNextStep($run, $nextNodeId, $node->config);
            } else {
                // Immediate dispatch for other nodes
                $run->update(['current_node_id' => $nextNodeId]);
                ProcessAutomationNode::dispatch($run, $nextNodeId);
            }

        } catch (\Exception $e) {
            $this->failRun($run, $e->getMessage());
            Log::error("Automation Error: " . $e->getMessage(), ['run_id' => $run->id, 'node_id' => $node->id]);
        }
    }

    /**
     * Schedule the next step with an optional delay.
     */
    protected function scheduleNextStep(AutomationRun $run, int $nextNodeId, array $config): void
    {
        $delayValue = (int)($config['delay_value'] ?? 0);
        $delayUnit = $config['delay_unit'] ?? 'seconds';
        
        $delay = match($delayUnit) {
            'minutes' => now()->addMinutes($delayValue),
            'hours' => now()->addHours($delayValue),
            'days' => now()->addDays($delayValue),
            default => now()->addSeconds($delayValue),
        };

        $run->update([
            'status' => 'delayed',
            'current_node_id' => $nextNodeId,
            'metadata' => array_merge($run->metadata ?? [], [
                'delayed_until' => $delay->toDateTimeString(),
                'delay_source_node' => $run->current_node_id
            ])
        ]);

        ProcessAutomationNode::dispatch($run, $nextNodeId)->delay($delay);
    }

    /**
     * Execute a single node's business logic.
     */
    protected function executeNode(AutomationNode $node, array &$context, AutomationRun $run): array|string
    {
        if ($node->type === 'trigger') {
            return 'success';
        }

        if ($node->type === 'condition') {
            $config = $node->config ?? [];
            $groups = $config['rule_groups'] ?? [];
            $match = $this->ruleEvaluator->evaluateGroups($groups, $context);
            return $match ? 'true' : 'false';
        }

        if ($node->type === 'action') {
            return $this->performAction($node, $context, $run);
        }

        if ($node->type === 'wait') {
            return 'success';
        }

        return 'success';
    }

    /**
     * Perform a real world action.
     */
    protected function performAction(AutomationNode $node, array &$context, AutomationRun $run): array|string
    {
        $config = $node->config ?? [];
        $subtype = $node->subtype;

        if ($subtype === 'whatsapp_message') {
            $outcome = $this->sendWhatsAppMessage($node, $context);
            return [
                'outcome' => $outcome,
                'output' => ['last_action_status' => $outcome] // Example of merging output
            ];
        }

        return 'success';
    }

    /**
     * Resolve variables in a string given a context.
     */
    protected function resolve($expression, array $context)
    {
        if (empty($expression)) return '';
        
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($context) {
            $field = trim($matches[1]);
            // Optional: Support removing 'trigger.' prefix if user provided it manually
            if (str_starts_with($field, 'trigger.')) {
                $field = substr($field, 8);
            }
            return $this->ruleEvaluator->getValueFromContext($field, $context) ?? $matches[0];
        }, $expression);
    }

    /**
     * Build and send a WhatsApp message.
     */
    protected function sendWhatsAppMessage(AutomationNode $node, array $context): string
    {
        $config = $node->config ?? [];
        $mode = $config['message_mode'] ?? 'text';
        
        $recipientExpression = $config['recipient_expression'] ?? '{{trigger.phone_number}}';
        $recipient = $this->resolve($recipientExpression, $context);
        
        if (empty($recipient)) {
            return 'failed_no_recipient';
        }

        $conversation = Conversation::firstOrCreate(
            ['contact_phone' => $recipient, 'company_id' => $node->flow->company_id],
            [
                'whatsapp_phone_number_id' => $config['provider_phone_number_id'] ?? null,
                'contact_name' => $this->resolve('{{trigger.name}}', $context) ?: $recipient,
            ]
        );

        $message = new ConversationMessage([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'status' => 'pending',
        ]);

        if ($mode === 'template') {
            $message->message_type = 'template';
            $message->body = $config['template_name'] ?? '';
            
            // Build components from mappings
            $mappings = $config['template_variable_mappings'] ?? [];
            $components = [];

            // Body variables
            if (!empty($mappings['body'])) {
                $parameters = [];
                foreach ($mappings['body'] as $index => $expr) {
                    $parameters[] = [
                        'type' => 'text',
                        'text' => $this->resolve($expr, $context)
                    ];
                }
                $components[] = [
                    'type' => 'body',
                    'parameters' => $parameters
                ];
            }

            // Header variables (text only for now)
            if (!empty($mappings['header'])) {
                $parameters = [];
                foreach ($mappings['header'] as $index => $expr) {
                    $parameters[] = [
                        'type' => 'text',
                        'text' => $this->resolve($expr, $context)
                    ];
                }
                $components[] = [
                    'type' => 'header',
                    'parameters' => $parameters
                ];
            }

            $message->meta_payload = [
                'template_name' => $config['template_name'] ?? '',
                'language_code' => $config['template_language'] ?? 'en_US',
                'components' => $components
            ];
        } else {
            $message->message_type = 'text';
            $message->body = $this->resolve($config['message_body'] ?? '', $context);
        }

        $message->save();

        // Real dispatch
        try {
            $success = $this->whatsappService->sendConversationMessage($message);
            return $success ? 'success' : 'failed';
        } catch (\Exception $e) {
            return 'failed';
        }
    }

    public function handleJobFailure(AutomationRun $run, \Throwable $exception): void
    {
        $this->failRun($run, "Job failed after retries: " . $exception->getMessage());
    }

    protected function failRun(AutomationRun $run, string $error): void
    {
        $run->update([
            'status' => 'failed',
            'last_error' => $error,
            'completed_at' => now(),
        ]);
        Log::error("Automation Run Failed: $error", ['run_id' => $run->id]);
    }

    protected function completeRun(AutomationRun $run): void
    {
        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        
        $run->flow->increment('total_executions');
        $run->flow->update(['last_run_at' => now()]);
    }
}
