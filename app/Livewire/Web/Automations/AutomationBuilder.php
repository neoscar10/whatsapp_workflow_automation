<?php

namespace App\Livewire\Web\Automations;

use App\Models\AutomationConnection;
use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\Automations\FlowValidator;
use Livewire\Component;

#[Title('Automation Builder')]
class AutomationBuilder extends Component
{
    public ?AutomationFlow $automation = null;
    public Collection $nodes;
    public Collection $connections;
    public Collection $triggerDefinitions;

    public ?int $selectedNodeId = null;
    public array $nodeConfig = [];
    public string $workflowName = '';
    public bool $showCustomTriggerModal = false;
    public string $newTriggerName = '';
    public string $newTriggerDescription = '';

    public array $canvasMeta = [
        'zoom' => 100,
        'pan_x' => 0,
        'pan_y' => 0,
    ];

    public function getResolvedSections(): array
    {
        if (!$this->selectedNodeId) {
            return ['empty_state'];
        }
 
        $node = $this->nodes->firstWhere('id', $this->selectedNodeId);
        if (!$node) return ['empty_state'];

        // Determine subtype based on current config if available (for reactive UI)
        $subtype = $node->subtype;
        
        // Safety check: Ensure webhook provisioning is present if needed before rendering sections
        if ($node->type === 'trigger' && in_array($subtype, ['webhook', 'webhook_api'])) {
            if (empty($this->nodeConfig['webhook_url']) || empty($this->nodeConfig['api_key_masked'])) {
                $this->ensureWebhookProvisioned();
            }
        }

        if ($node->type === 'trigger' && isset($this->nodeConfig['trigger_category'])) {
            $subtype = $this->nodeConfig['trigger_category'];
        } elseif ($node->type === 'loop' && isset($this->nodeConfig['loop_type'])) {
            $subtype = $this->nodeConfig['loop_type'];
        } elseif ($node->type === 'wait' && isset($this->nodeConfig['wait_mode'])) {
            $subtype = $this->nodeConfig['wait_mode'];
        }

        return match ($node->type . '.' . $subtype) {
            'action.whatsapp_message' => [
                'action_type',
                'provider_account_selector',
                'message_mode_selector',
                ($this->nodeConfig['message_mode'] ?? 'text') === 'template' ? 'template_selector' : 'message_editor',
                ($this->nodeConfig['message_mode'] ?? 'text') === 'template' ? 'template_variable_mapper' : null,
                'advanced_settings',
                'error_handling'
            ],
            'action.call_api' => [
                'action_type',
                'action_api_call',
                'advanced_settings',
                'error_handling'
            ],
            'action.send_email' => [
                'action_type',
                'action_email_form',
                'advanced_settings',
                'error_handling'
            ],
            'action.update_row' => [
                'action_type',
                'action_update_data',
                'advanced_settings',
                'error_handling'
            ],
            'action.collect_input' => [
                'action_type',
                'action_collect_input',
                'advanced_settings'
            ],
            'condition.if_else', 'condition.check_keyword', 'condition.generic' => [
                'rule_group_builder',
                'outcome_settings'
            ],
            'wait.delay' => [
                'wait_time_delay_input',
                'wait_condition_based',
                'wait_advanced_settings',
                'wait_architect_tip',
            ],
            'wait.condition_based' => [
                'wait_condition_based',
                'wait_advanced_settings'
            ],
            'loop.fixed', 'loop.condition_based' => [
                'loop_type_selector',
                'loop_condition_builder',
                'loop_max_iteration_limit',
                'loop_execution_behavior',
                'loop_failure_handling',
                'loop_natural_language_preview',
            ],
            'loop.iterate_over_data' => [
                'loop_type_selector',
                'loop_iteration_parameters',
                'loop_execution_behavior_data',
                'loop_available_variables',
                'loop_reliability_settings',
                'loop_iteration_preview',
            ],
            'trigger.time_based', 'trigger.event_based', 'trigger.behavior_based', 'trigger.behavior', 'trigger.conditional', 'trigger.webhook', 'trigger.webhook_api' => [
                'trigger_type_selector_compact',
                'trigger_definition_selector',
                'trigger_config_router', // New router partial to handle subtype forms
                'trigger_output_variables',
            ],
            'parallel.generic' => [
                'parallel_config'
            ],
            'finish.end' => [
                'finish_config'
            ],
            default => [
                'generic_settings'
            ],
        };
    }

    public function mount($id = null)
    {
        $companyId = auth()->user()->company_id;

        if ($id) {
            $this->automation = AutomationFlow::where('company_id', $companyId)->findOrFail($id);
            $this->workflowName = $this->automation->name;
            $this->canvasMeta = $this->automation->canvas_meta ?: $this->canvasMeta;
        } else {
            $this->automation = new AutomationFlow([
                'company_id' => $companyId,
                'name' => 'Untitled Workflow',
                'status' => 'draft',
                'created_by_user_id' => auth()->id(),
            ]);
            $this->workflowName = $this->automation->name;
        }

        $this->loadState();
        $this->triggerDefinitions = \App\Models\AutomationTriggerDefinition::forCompany($companyId)->get();
    }

    public function loadState()
    {
        if ($this->automation && $this->automation->exists) {
            $this->nodes = $this->automation->nodes()->get();
            $this->connections = $this->automation->connections()->get();
        } else {
            $this->nodes = collect();
            $this->connections = collect();
        }
    }

    public function addNode(string $type, string $subtype)
    {
        // Simple placement logic: relative to selection or center
        $x = 400; 
        $y = 300; 

        if ($this->selectedNodeId) {
            $selectedNode = $this->nodes->firstWhere('id', $this->selectedNodeId);
            if ($selectedNode) {
                $x = $selectedNode->position_x;
                $y = $selectedNode->position_y + 150;
            }
        } elseif ($lastNode = $this->nodes->last()) {
            $x = $lastNode->position_x + 300;
            $y = $lastNode->position_y;
        }

        // Ensure automation exists first
        if (!$this->automation->exists) {
            $this->saveDraft(); 
            // In Livewire, saveDraft redirects, but we can't easily prevent that here.
            // However, most library clicks happen in an existing flow.
        }

        $node = $this->automation->nodes()->create([
            'type' => $type,
            'subtype' => $subtype,
            'label' => $this->getDefaultLabel($subtype),
            'position_x' => $x,
            'position_y' => $y,
            'config' => $type === 'trigger' ? [
                'trigger_category' => $subtype,
                'trigger_type' => $subtype,
                'trigger_definition_key' => $subtype === 'event_based' ? 'new_message_received' : null
            ] : []
        ]);

        $this->loadState(); // Refresh to ensure it's in the collection for immediate selection
        $this->selectNode($node->id);
    }

    public function updatedNodeConfigTriggerCategory($value)
    {
        $this->nodeConfig['trigger_type'] = $value;
        $this->nodeConfig['trigger_definition_key'] = $value === 'event_based' ? 'new_message_received' : null;
        
        if ($this->nodeConfig['trigger_definition_key']) {
            $this->updatedNodeConfigTriggerDefinitionKey($this->nodeConfig['trigger_definition_key']);
        }
    }

    public function openCustomTriggerModal()
    {
        $this->showCustomTriggerModal = true;
        $this->newTriggerName = '';
        $this->newTriggerDescription = '';
    }

    public function saveCustomTrigger()
    {
        $this->validate([
            'newTriggerName' => 'required|min:3|max:50',
            'nodeConfig.trigger_category' => 'required',
        ]);

        $companyId = auth()->user()->company_id;
        $category = $this->nodeConfig['trigger_category'];

        $definition = \App\Models\AutomationTriggerDefinition::create([
            'company_id' => $companyId,
            'name' => $this->newTriggerName,
            'description' => $this->newTriggerDescription,
            'category' => $category,
            'key' => \Illuminate\Support\Str::slug($this->newTriggerName) . '_' . time(),
            'default_config' => $this->getInitialConfigForCategory($category),
            'default_output_variables' => $this->getInitialVariablesForCategory($category),
            'status' => true,
            'is_system' => false,
            'is_read_only' => false,
            'created_by_user_id' => auth()->id(),
        ]);

        // Refresh triggers
        $this->triggerDefinitions = \App\Models\AutomationTriggerDefinition::forCompany($companyId)->get();
        
        // Select the new trigger
        $this->nodeConfig['trigger_definition_key'] = $definition->key;
        $this->updatedNodeConfigTriggerDefinitionKey($definition->key);

        $this->showCustomTriggerModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Custom trigger created!']);
    }

    protected function getInitialConfigForCategory(string $category): array
    {
        return match($category) {
            'webhook_api', 'webhook' => [
                'webhook_uuid' => (string) \Illuminate\Support\Str::uuid(),
                'webhook_secret' => \Illuminate\Support\Str::random(32)
            ],
            'time_based' => ['repeat_interval' => 'once'],
            'conditional' => ['rules' => [['field' => 'user.tags', 'operator' => 'contains', 'value' => '']]],
            default => []
        };
    }

    protected function getInitialVariablesForCategory(string $category): array
    {
        return match($category) {
            'webhook_api', 'webhook' => [
                ['key' => 'phone_number', 'type' => 'STRING'],
                ['key' => 'message_body', 'type' => 'STRING'],
            ],
            'event_based' => [
                ['key' => 'event_type', 'type' => 'STRING'],
                ['key' => 'timestamp', 'type' => 'DATETIME'],
            ],
            default => []
        };
    }

    public function selectNode(?int $id)
    {
        $this->selectedNodeId = $id;

        if ($id) {
            $node = $this->nodes->firstWhere('id', $id);
            $this->nodeConfig = $node->config ?: [];
            
            // Ensure defaults for Actions
            if ($node->type === 'action') {
                $this->nodeConfig['action_type'] = $this->nodeConfig['action_type'] ?? ($node->subtype === 'whatsapp_message' ? 'send_message' : $node->subtype);
                
                if ($node->subtype === 'whatsapp_message') {
                    $this->nodeConfig['recipient_expression'] = $this->nodeConfig['recipient_expression'] ?? '{{trigger.phone_number}}';
                    $this->nodeConfig['message_mode'] = $this->nodeConfig['message_mode'] ?? 'text';
                    $this->nodeConfig['message_body'] = $this->nodeConfig['message_body'] ?? '';
                    $this->nodeConfig['provider_account_id'] = $this->nodeConfig['provider_account_id'] ?? null;
                    $this->nodeConfig['template_id'] = $this->nodeConfig['template_id'] ?? null;
                    $this->nodeConfig['template_variable_mappings'] = $this->nodeConfig['template_variable_mappings'] ?? [];
                }

                if ($node->subtype === 'call_api') {
                    $this->nodeConfig['method'] = $this->nodeConfig['method'] ?? 'POST';
                    $this->nodeConfig['headers'] = $this->nodeConfig['headers'] ?? [['key' => 'Content-Type', 'value' => 'application/json']];
                }

                // Global Action defaults
                $this->nodeConfig['advanced_settings'] = $this->nodeConfig['advanced_settings'] ?? [];
                $this->nodeConfig['error_handling'] = $this->nodeConfig['error_handling'] ?? ['strategy' => 'retry'];
            }

            // Ensure defaults for Conditions
            if ($node->type === 'condition') {
                $this->nodeConfig['match_mode'] = $this->nodeConfig['match_mode'] ?? 'all';
                
                // Critical: Ensure rule_groups and nested rules exist for the builder view
                if (empty($this->nodeConfig['rule_groups'])) {
                    $this->nodeConfig['rule_groups'] = [
                        [
                            'joiner' => 'and',
                            'rules' => [
                                ['field' => 'message_body', 'operator' => 'contains', 'value' => '']
                            ]
                        ]
                    ];
                }

                // Sanitize: Ensure every group has a 'rules' array
                foreach ($this->nodeConfig['rule_groups'] as $idx => $group) {
                    if (empty($group['rules'])) {
                        $this->nodeConfig['rule_groups'][$idx]['rules'] = [['field' => 'message_body', 'operator' => 'contains', 'value' => '']];
                    }
                }
            }

            $this->dispatch('nodeSelected', nodeId: $id);

            // Ensure defaults for Wait
            if ($node->type === 'wait') {
                $this->nodeConfig['wait_mode'] = $this->nodeConfig['wait_mode'] ?? 'delay';
                $this->nodeConfig['delay_value'] = $this->nodeConfig['delay_value'] ?? 30;
                $this->nodeConfig['delay_unit'] = $this->nodeConfig['delay_unit'] ?? 'minutes';
            }

            // Ensure defaults for Triggers
            if ($node->type === 'trigger') {
                $category = $this->nodeConfig['trigger_category'] ?? $node->subtype;
                
                // Map 'webhook' to 'webhook_api' for consistency
                if ($category === 'webhook') $category = 'webhook_api';

                // AUTO-REPAIR: If category is blank in DB but we know it's a trigger,
                // populate it and sync back to ensure persistence.
                if (empty($this->nodeConfig['trigger_category'])) {
                    Log::info("Auto-repairing trigger node [{$node->id}] in Builder: Missing category", ['subtype' => $node->subtype]);
                    $this->nodeConfig['trigger_category'] = $category;
                    $this->nodeConfig['trigger_type'] = $this->nodeConfig['trigger_type'] ?? $category;
                    
                    if ($category === 'event_based') {
                        $this->nodeConfig['trigger_definition_key'] = 'new_message_received';
                    }

                    $node->config = $this->nodeConfig;
                    $node->save();
                }

                if ($this->nodeConfig['trigger_category'] === 'event_based' && in_array($this->nodeConfig['trigger_definition_key'] ?? '', ['event_based', '', null])) {
                    Log::info("Auto-repairing trigger node [{$node->id}] in Builder: Correcting definition key to new_message_received", [
                        'old_val' => $this->nodeConfig['trigger_definition_key'] ?? 'null'
                    ]);
                    $this->nodeConfig['trigger_definition_key'] = 'new_message_received';
                    $node->config = $this->nodeConfig;
                    $node->save();
                }

                $this->nodeConfig['trigger_category'] = $category;
                $this->nodeConfig['trigger_type'] = $this->nodeConfig['trigger_type'] ?? $category;

                $defKey = $this->nodeConfig['trigger_definition_key'] ?? null;
                if ($defKey) {
                    $definition = $this->triggerDefinitions->firstWhere('key', $defKey);
                    if ($definition) {
                        $this->nodeConfig['trigger_type'] = $definition->category;
                        $this->nodeConfig['output_variables'] = array_merge(
                            $definition->default_output_variables ?? [],
                            $this->nodeConfig['detected_variables'] ?? []
                        );
                    }
                }
                
                if ($this->nodeConfig['trigger_category'] === 'event_based') {
                    // Auto-default to New Message Received if it's a fresh event trigger
                    $this->nodeConfig['trigger_definition_key'] = $this->nodeConfig['trigger_definition_key'] ?? 'new_message_received';
                    
                    // Manually trigger the definition update to pull in variables
                    $this->updatedNodeConfigTriggerDefinitionKey($this->nodeConfig['trigger_definition_key']);
                }

                if (in_array($node->subtype, ['webhook', 'webhook_api'])) {
                    $this->ensureWebhookProvisioned();
                }

                if ($node->subtype === 'conditional' || ($this->nodeConfig['trigger_type'] ?? '') === 'conditional') {
                    $this->nodeConfig['rules'] = $this->nodeConfig['rules'] ?? [
                        ['field' => 'user.tags', 'operator' => 'contains', 'value' => 'premium_user']
                    ];
                }
            }

            // Ensure defaults for Loops
            if ($node->type === 'loop') {
                $this->nodeConfig['loop_type'] = $this->nodeConfig['loop_type'] ?? $node->subtype;
                $this->nodeConfig['max_iteration_limit'] = $this->nodeConfig['max_iteration_limit'] ?? 100;
            }

            // Parallel & Finish
            if ($node->type === 'parallel') {
                $this->nodeConfig['branch_count'] = $this->nodeConfig['branch_count'] ?? 2;
            }
            if ($node->type === 'finish') {
                $this->nodeConfig['end_state'] = $this->nodeConfig['end_state'] ?? 'success';
            }
        } else {
            $this->nodeConfig = [];
        }
    }

    public function addRule(int $groupIndex)
    {
        $this->nodeConfig['rule_groups'][$groupIndex]['rules'][] = [
            'field' => 'message_body',
            'operator' => 'contains',
            'value' => ''
        ];
    }

    public function removeRule(int $groupIndex, int $ruleIndex)
    {
        unset($this->nodeConfig['rule_groups'][$groupIndex]['rules'][$ruleIndex]);
        // Reset keys for rules array
        $this->nodeConfig['rule_groups'][$groupIndex]['rules'] = array_values($this->nodeConfig['rule_groups'][$groupIndex]['rules']);
        
        // If no rules left, keep at least one empty
        if (empty($this->nodeConfig['rule_groups'][$groupIndex]['rules'])) {
            $this->addRule($groupIndex);
        }
    }

    // Trigger Rule Management
    public function addTriggerRule()
    {
        $this->nodeConfig['rules'][] = ['field' => 'user.tags', 'operator' => 'contains', 'value' => ''];
    }

    public function removeTriggerRule($index)
    {
        if (isset($this->nodeConfig['rules'][$index])) {
            unset($this->nodeConfig['rules'][$index]);
            $this->nodeConfig['rules'] = array_values($this->nodeConfig['rules']);
        }
    }

    public function updatedNodeConfigTriggerDefinitionKey($value)
    {
        $definition = $this->triggerDefinitions->firstWhere('key', $value);
        if ($definition) {
            $this->nodeConfig['trigger_type'] = $definition->category;
            $this->nodeConfig['output_variables'] = array_merge(
                $definition->default_output_variables ?? [],
                $this->nodeConfig['detected_variables'] ?? []
            );
            
            // Merge default config if not already set or if explicitly resetting
            $this->nodeConfig = array_merge($definition->default_config ?? [], $this->nodeConfig);
        }
    }

    public function updatedNodeConfigTemplateId($value)
    {
        if (!$value) return;

        $template = WhatsAppTemplate::find($value);
        if ($template) {
            $this->nodeConfig['template_name'] = $template->remote_template_name;
            $this->nodeConfig['template_language'] = $template->language_code;
            
            // Re-initialize mappings for the new template
            $mappings = [
                'body' => [],
                'header' => []
            ];

            // Extract body variables: {{1}}, {{2}}...
            preg_match_all('/\{\{(\d+)\}\}/', $template->body_text, $bodyMatches);
            if (!empty($bodyMatches[1])) {
                foreach ($bodyMatches[1] as $num) {
                    $mappings['body'][$num] = $this->nodeConfig['template_variable_mappings']['body'][$num] ?? '';
                }
            }

            // Extract header variables if text
            if ($template->header_type === 'text' && !empty($template->header_text)) {
                preg_match_all('/\{\{(\d+)\}\}/', $template->header_text, $headerMatches);
                if (!empty($headerMatches[1])) {
                    foreach ($headerMatches[1] as $num) {
                        $mappings['header'][$num] = $this->nodeConfig['template_variable_mappings']['header'][$num] ?? '';
                    }
                }
            }

            $this->nodeConfig['template_variable_mappings'] = $mappings;
        }
    }

    protected function ensureWebhookProvisioned()
    {
        $updated = false;
        if (empty($this->nodeConfig['webhook_uuid'])) {
            $this->nodeConfig['webhook_uuid'] = (string) \Illuminate\Support\Str::uuid();
            $updated = true;
        }
        if (empty($this->nodeConfig['webhook_secret'])) {
            $this->nodeConfig['webhook_secret'] = \Illuminate\Support\Str::random(32);
            $updated = true;
        }
        
        // Always ensure these derived values are fresh
        $this->nodeConfig['webhook_url'] = route('api.automation.webhook', ['uuid' => $this->nodeConfig['webhook_uuid']]);
        $this->nodeConfig['api_key_masked'] = substr($this->nodeConfig['webhook_secret'], 0, 8) . '****************' . substr($this->nodeConfig['webhook_secret'], -4);
        
        // Force the array to be recognized as changed by Livewire
        $this->nodeConfig = array_merge([], $this->nodeConfig);

        // Sync back to the model in the collection
        if ($this->selectedNodeId) {
            $node = $this->nodes->firstWhere('id', $this->selectedNodeId);
            if ($node) {
                $node->config = $this->nodeConfig;
                // If we generated new base metadata (UUID/Secret), save immediately to avoid loss
                if ($updated) {
                    $node->save();
                }
            }
        }
    }

    public function sendTestEvent()
    {
        if (!$this->selectedNodeId) return;

        $node = \App\Models\AutomationNode::find($this->selectedNodeId);
        $config = $node->config ?? [];

        if (!empty($config['last_test_payload'])) {
            $this->nodeConfig['last_test_payload'] = $config['last_test_payload'];
            $this->nodeConfig['detected_variables'] = $config['detected_variables'] ?? [];
            $this->nodeConfig['last_received_at'] = $config['last_received_at'] ?? now()->toDateTimeString();
            
            $count = count($this->nodeConfig['detected_variables']);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Real event detected! {$count} variables identified."
            ]);
        } else {
            // Support Auto Reply Demo Fallback
            $this->nodeConfig['detected_variables'] = [
                ['key' => 'phone_number', 'type' => 'STRING'],
                ['key' => 'sender_phone', 'type' => 'STRING'],
                ['key' => 'name', 'type' => 'STRING'],
                ['key' => 'message_body', 'type' => 'STRING'],
            ];

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'No real event found yet. Showing demo variables.'
            ]);
        }
    }

    public function saveNodeConfig()
    {
        if (!$this->selectedNodeId) return;

        $node = AutomationNode::findOrFail($this->selectedNodeId);
        
        // Persist subtype changes if they happened in config
        if ($node->type === 'trigger' && isset($this->nodeConfig['trigger_category'])) {
            $node->subtype = $this->nodeConfig['trigger_category'];
        } elseif ($node->type === 'loop' && isset($this->nodeConfig['loop_type'])) {
            $node->subtype = $this->nodeConfig['loop_type'];
        } elseif ($node->type === 'wait' && isset($this->nodeConfig['wait_mode'])) {
            $node->subtype = $this->nodeConfig['wait_mode'];
        }

        \Illuminate\Support\Facades\Log::info("AUDIT: Pre-Save Trigger Config", [
            'node_id' => $node->id,
            'config' => $this->nodeConfig
        ]);

        $node->config = $this->nodeConfig;
        $node->save();

        \Illuminate\Support\Facades\Log::info("AUDIT: Post-Save Verfication", [
            'node_id' => $node->id,
            'stored_config' => $node->refresh()->config
        ]);

        // Update local collection
        $index = $this->nodes->search(fn($n) => $n->id === $this->selectedNodeId);
        if ($index !== false) {
            $this->nodes[$index] = $node;
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Node configuration saved']);
        $this->selectedNodeId = null;
    }

    public function updateNodePosition(int $id, int $x, int $y)
    {
        $node = AutomationNode::findOrFail($id);
        $node->update([
            'position_x' => $x,
            'position_y' => $y,
        ]);

        // Update local state without full reload
        $this->nodes = $this->nodes->map(function($n) use ($id, $x, $y) {
            if ($n->id === $id) {
                $n->position_x = (int) $x;
                $n->position_y = (int) $y;
            }
            return $n;
        });

        // We don't necessarily need to reload connections here because 
        // the frontend will calculate paths based on the updated node positions.
    }

    public function connectNodes(int $sourceNodeId, int $targetNodeId, string $sourceHandle = 'bottom', string $targetHandle = 'top', ?string $conditionKey = null)
    {
        // 1. Validation
        if ($sourceNodeId === $targetNodeId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot connect a node to itself']);
            return;
        }

        // 2. Check for existing identical connection
        $exists = AutomationConnection::where([
            'automation_flow_id' => $this->automation->id,
            'source_node_id' => $sourceNodeId,
            'target_node_id' => $targetNodeId,
            'source_handle' => $sourceHandle,
            'condition_key' => $conditionKey,
        ])->exists();

        if ($exists) {
            $this->dispatch('notify', ['type' => 'info', 'message' => 'Connection already exists']);
            return;
        }

        // 3. Create Connection
        $connection = $this->automation->connections()->create([
            'source_node_id' => $sourceNodeId,
            'target_node_id' => $targetNodeId,
            'source_handle' => $sourceHandle,
            'target_handle' => $targetHandle,
            'condition_key' => $conditionKey,
            'meta' => [],
        ]);

        // 4. Update local state
        $this->connections->push($connection);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Nodes connected']);
    }

    public function removeConnection(int $id)
    {
        $connection = AutomationConnection::findOrFail($id);
        $connection->delete();

        $this->connections = $this->connections->reject(fn($c) => $c->id === $id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Connection removed']);
    }

    public function deleteNode()
    {
        if (!$this->selectedNodeId) return;

        $node = AutomationNode::findOrFail($this->selectedNodeId);
        $node->delete();

        $this->nodes = $this->nodes->reject(fn($n) => $n->id === $this->selectedNodeId);
        $this->connections = $this->connections->reject(fn($c) => 
            $c->source_node_id === $this->selectedNodeId || $c->target_node_id === $this->selectedNodeId
        );

        $this->selectedNodeId = null;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Node removed']);
    }

    public function saveDraft()
    {
        $this->automation->company_id = auth()->user()->company_id;
        $this->automation->name = $this->workflowName;
        $this->automation->canvas_meta = $this->canvasMeta;
        $this->automation->save();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Workflow draft saved']);
        
        // If it was a new flow, we might need to redirect to have the ID in URL
        return redirect()->route('automations.edit', $this->automation->id);
    }

    public function publish(FlowValidator $validator)
    {
        // Save current state first to ensure we validate the latest data
        $this->automation->company_id = auth()->user()->company_id;
        $this->automation->name = $this->workflowName;
        $this->automation->canvas_meta = $this->canvasMeta;
        $this->automation->save();

        $result = $validator->validate($this->automation);

        if (!$result['is_valid']) {
            foreach ($result['errors'] as $error) {
                $this->dispatch('notify', ['type' => 'error', 'message' => $error]);
            }
            return;
        }

        $this->automation->company_id = auth()->user()->company_id;
        $this->automation->name = $this->workflowName;
        $this->automation->status = 'active';
        $this->automation->is_enabled = true;
        $this->automation->save();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Workflow published successfully']);
        return redirect()->route('automations.index');
    }

    protected function getDefaultLabel(string $subtype): string
    {
        return match($subtype) {
            'event_based' => 'New Message Received',
            'webhook' => 'Incoming Webhook',
            'time_based' => 'Scheduled Trigger',
            'conditional' => 'Conditional Trigger',
            'whatsapp_message' => 'WhatsApp Message',
            'call_api' => 'API Call',
            'send_email' => 'Send Email',
            'update_row' => 'Update Data',
            'collect_input' => 'Collect Input',
            'if_else' => 'Split Condition',
            'delay' => 'Wait Delay',
            'loop', 'iterate_over_data' => 'Loop / Iterate',
            'parallel' => 'Parallel Path',
            'end', 'finish' => 'End Session',
            default => ucfirst(str_replace('_', ' ', $subtype))
        };
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        $companyId = auth()->user()->company_id;

        return view('livewire.web.automations.builder', [
            'availableAccounts' => WhatsAppAccount::where('company_id', $companyId)->with('phoneNumbers')->get(),
            'availableTemplates' => WhatsAppTemplate::where('company_id', $companyId)
                ->where('status', 'approved')
                ->orderBy('remote_template_name')
                ->get(),
        ]);
    }
}
