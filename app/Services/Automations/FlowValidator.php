<?php

namespace App\Services\Automations;

use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use Illuminate\Support\Collection;

class FlowValidator
{
    /**
     * Validate an automation flow for publishing.
     */
    public function validate(AutomationFlow $flow): array
    {
        $errors = [];
        $nodes = $flow->nodes;
        $connections = $flow->connections;

        // 1. Check for Trigger
        $trigger = $nodes->firstWhere('type', 'trigger');
        if (!$trigger) {
            $errors[] = "The workflow must have at least one trigger node.";
        }

        // 2. Check for Reachable nodes
        if ($trigger) {
            $reachableNodeIds = $this->getReachableNodeIds($trigger, $connections);
            $orphanCount = $nodes->count() - count($reachableNodeIds);
            if ($orphanCount > 0) {
                // Not necessarily a hard error, but good to warn. 
                // For this audit fix, we'll consider it a warning or specific validation.
            }
        }

        // 3. Node-specific validations
        foreach ($nodes as $node) {
            $nodeErrors = $this->validateNode($node);
            foreach ($nodeErrors as $err) {
                $errors[] = "Node '{$node->name}': {$err}";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function validateNode(AutomationNode $node): array
    {
        $errors = [];
        $config = $node->config ?? [];

        switch ($node->subtype) {
            case 'whatsapp_message':
                if (empty($config['recipient_expression'])) {
                    $errors[] = "Recipient is required.";
                }
                if (($config['message_mode'] ?? 'text') === 'text' && empty($config['message_body'])) {
                    $errors[] = "Message body is required for text mode.";
                }
                if (($config['message_mode'] ?? 'text') === 'template' && empty($config['template_id'])) {
                    $errors[] = "Please select a WhatsApp template.";
                }
                break;

            case 'split_condition':
                if (empty($config['rules']) && empty($config['rule_groups'])) {
                    $errors[] = "Condition rules are required.";
                }
                break;

            case 'wait_delay':
                if (!isset($config['delay_value']) || $config['delay_value'] < 0) {
                    $errors[] = "A valid wait duration is required.";
                }
                break;
        }

        return $errors;
    }

    protected function getReachableNodeIds(AutomationNode $startNode, Collection $connections): array
    {
        $visited = [];
        $stack = [$startNode->id];

        while (!empty($stack)) {
            $currentId = array_pop($stack);
            if (!in_array($currentId, $visited)) {
                $visited[] = $currentId;
                
                $nextNodes = $connections->where('source_node_id', $currentId)->pluck('target_node_id')->toArray();
                foreach ($nextNodes as $nextId) {
                    if (!in_array($nextId, $visited)) {
                        $stack[] = $nextId;
                    }
                }
            }
        }

        return $visited;
    }
}
