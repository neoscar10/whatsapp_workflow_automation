<?php

namespace App\Livewire\Web\Automations;

use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\AutomationConnection;
use App\Models\SimulationSession;
use App\Models\SimulationStep;
use App\Models\SimulationBreakpoint;
use App\Services\Automations\AutomationSimulationService;
use Livewire\Component;
use Livewire\Attributes\Layout;

class AutomationSimulation extends Component
{
    public AutomationFlow $automation;
    public $nodes;
    public $connections;
    
    // Simulation State
    public ?SimulationSession $session = null;
    public $activeStepId = null;
    public $tab = 'execution'; // execution, variables, breakpoints, triggers, history
    // Payload Editor State
    public $payloadMode = 'form'; // form, json
    public $formPayload = []; // [['key' => '...', 'value' => '...']]
    public $suggestedFields = [];
    public $initialPayload = '{\n  "contact": {\n    "name": "John Doe",\n    "phone": "1234567890"\n  },\n  "trigger_source": "manual_test"\n}';
    
    public function mount(int $id)
    {
        $this->automation = AutomationFlow::where('company_id', auth()->user()->company_id)
            ->findOrFail($id);
            
        $this->loadFlowData();
        $this->detectWorkflowVariables();
        $this->syncJsonToForm();
    }

    public function loadFlowData()
    {
        $this->nodes = AutomationNode::where('automation_flow_id', $this->automation->id)->get();
        $this->connections = AutomationConnection::where('automation_flow_id', $this->automation->id)->get();
    }

    public function startSimulation()
    {
        $this->session = SimulationSession::create([
            'automation_flow_id' => $this->automation->id,
            'company_id' => auth()->user()->company_id,
            'status' => 'ready',
            'initial_payload' => json_decode($this->initialPayload, true) ?: [],
            'context' => json_decode($this->initialPayload, true) ?: [],
        ]);

        app(AutomationSimulationService::class)->start($this->session);
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Simulation started!']);
    }

    public function pauseSimulation()
    {
        if ($this->session?->status === 'running') {
            $this->session->update(['status' => 'paused']);
            $this->dispatch('notify', ['type' => 'info', 'message' => 'Simulation paused.']);
        }
    }

    public function resumeSimulation()
    {
        if ($this->session?->status === 'paused') {
            $this->session->update(['status' => 'running']);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Simulation resumed.']);
        }
    }

    public function runNextStep()
    {
        if (!$this->session || $this->session->status !== 'running') {
            return;
        }

        // Check for breakpoints
        $isBreakpoint = SimulationBreakpoint::where('automation_flow_id', $this->automation->id)
            ->where('node_id', $this->session->current_node_id)
            ->exists();

        // If it's a breakpoint and we HAVEN'T already paused here
        if ($isBreakpoint && ($this->session->meta['last_breakpoint'] ?? null) !== $this->session->current_node_id) {
            $this->session->update([
                'status' => 'paused',
                'meta' => array_merge($this->session->meta ?? [], ['last_breakpoint' => $this->session->current_node_id])
            ]);
            $this->dispatch('notify', ['type' => 'info', 'message' => 'Paused at breakpoint.']);
            return;
        }

        app(AutomationSimulationService::class)->executeNextStep($this->session);
        
        // Clear breakpoint flag if we just moved past it
        if (!$isBreakpoint && isset($this->session->meta['last_breakpoint'])) {
            $meta = $this->session->meta;
            unset($meta['last_breakpoint']);
            $this->session->update(['meta' => $meta]);
        }

        $this->session->refresh();
    }

    public function stopSimulation()
    {
        if ($this->session) {
            $this->session->update(['status' => 'stopped', 'completed_at' => now()]);
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Simulation stopped.']);
        }
    }

    public function selectStep($stepId)
    {
        $this->activeStepId = $stepId;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function toggleBreakpoint($nodeId)
    {
        $breakpoint = SimulationBreakpoint::where('automation_flow_id', $this->automation->id)
            ->where('node_id', $nodeId)
            ->first();

        if ($breakpoint) {
            $breakpoint->delete();
        } else {
            SimulationBreakpoint::create([
                'automation_flow_id' => $this->automation->id,
                'node_id' => $nodeId,
                'is_enabled' => true,
            ]);
        }
    }

    public function detectWorkflowVariables()
    {
        $variables = [];
        $nodes = AutomationNode::where('automation_flow_id', $this->automation->id)->get();

        foreach ($nodes as $node) {
            $config = $node->config ?? [];
            
            // 1. Detect from strings ({{var}})
            $jsonConfig = json_encode($config);
            preg_match_all('/\{\{([\s\w\.]*)\}\}/', $jsonConfig, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $cleaned = trim($match);
                    if ($cleaned) $variables[] = $cleaned;
                }
            }

            // 2. Detect from condition fields
            if ($node->type === 'condition') {
                $rules = $config['rules'] ?? [];
                foreach ($rules as $rule) {
                    if (!empty($rule['field'])) $variables[] = $rule['field'];
                }
                
                $groups = $config['rule_groups'] ?? [];
                foreach ($groups as $group) {
                    foreach (($group['rules'] ?? []) as $rule) {
                        if (!empty($rule['field'])) $variables[] = $rule['field'];
                    }
                }
            }
        }

        // Clean: Remove 'trigger.' prefix if present, then unique
        $this->suggestedFields = collect($variables)
            ->map(fn($v) => str_starts_with($v, 'trigger.') ? substr($v, 8) : $v)
            ->map(fn($v) => str_starts_with($v, 'payload.') ? substr($v, 8) : $v)
            ->unique()
            ->values()
            ->toArray();
    }

    public function syncJsonToForm()
    {
        $data = json_decode($this->initialPayload, true) ?: [];
        $this->formPayload = [];
        $this->flattenJson($data);
        
        if (empty($this->formPayload)) {
            $this->addPayloadField(); 
        }
    }

    protected function flattenJson(array $array, string $prefix = '')
    {
        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value) && !empty($value) && !$this->isList($value)) {
                $this->flattenJson($value, $fullKey);
            } else {
                $this->formPayload[] = ['key' => $fullKey, 'value' => is_array($value) ? json_encode($value) : $value];
            }
        }
    }

    protected function isList(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    public function syncFormToJson()
    {
        $data = [];
        foreach ($this->formPayload as $entry) {
            if (empty($entry['key'])) continue;
            
            $keys = explode('.', $entry['key']);
            $current = &$data;
            
            foreach ($keys as $i => $key) {
                if ($i === count($keys) - 1) {
                    $val = $entry['value'];
                    // Try to decode if it looks like JSON array/object
                    if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
                        $decoded = json_decode($val, true);
                        if (json_last_error() === JSON_ERROR_NONE) $val = $decoded;
                    }
                    $current[$key] = $val;
                } else {
                    if (!isset($current[$key]) || !is_array($current[$key])) {
                        $current[$key] = [];
                    }
                    $current = &$current[$key];
                }
            }
        }
        
        $this->initialPayload = json_encode($data, JSON_PRETTY_PRINT);
    }

    public function addPayloadField()
    {
        $this->formPayload[] = ['key' => '', 'value' => ''];
    }

    public function removePayloadField($index)
    {
        unset($this->formPayload[$index]);
        $this->formPayload = array_values($this->formPayload);
        $this->syncFormToJson();
    }

    public function addSuggestedField($key)
    {
        // Don't add if already exists
        if (collect($this->formPayload)->pluck('key')->contains($key)) {
            return;
        }

        $this->formPayload[] = ['key' => $key, 'value' => ''];
        $this->syncFormToJson();
    }

    public function togglePayloadMode()
    {
        if ($this->payloadMode === 'form') {
            $this->syncFormToJson();
            $this->payloadMode = 'json';
        } else {
            $this->syncJsonToForm();
            $this->payloadMode = 'form';
        }
    }

    /**
     * Generate a human-friendly summary for a given step.
     */
    public function getStepSummary(SimulationStep $step): array
    {
        $summary = [];
        $node = AutomationNode::find($step->node_id);
        $output = $step->output_snapshot ?? [];

        if ($step->node_type === 'trigger') {
            $summary[] = ['label' => 'Trigger Type', 'value' => $step->node_subtype];
            $summary[] = ['label' => 'Source', 'value' => $step->input_snapshot['trigger_source'] ?? 'External Webhook'];
            if (!empty($step->input_snapshot['contact'])) {
                $summary[] = ['label' => 'Contact', 'value' => $step->input_snapshot['contact']['name'] ?? $step->input_snapshot['contact']['phone'] ?? 'Unknown'];
            }
        }

        if ($step->node_type === 'action') {
            if ($step->node_subtype === 'whatsapp_message') {
                $summary[] = ['label' => 'Action', 'value' => 'WhatsApp Message'];
                $summary[] = ['label' => 'Recipient', 'value' => $output['recipient'] ?? 'Unknown'];
                $summary[] = ['label' => 'Status', 'value' => $output['status'] ?? 'Success'];
                
                if (($output['message_mode'] ?? 'text') === 'template') {
                    $summary[] = ['label' => 'Template', 'value' => $output['template_name'] ?? 'Unknown'];
                    if (!empty($output['preview']['body'])) {
                        $summary[] = ['label' => 'Message Preview', 'value' => $output['preview']['body']];
                    }
                } else {
                    $summary[] = ['label' => 'Message Body', 'value' => $output['simulated_payload'] ?? 'No content'];
                }
            }
        }

        if ($step->node_type === 'condition') {
            $matched = $output['match'] ?? false;
            $summary[] = ['label' => 'Evaluation Result', 'value' => $matched ? 'TRUE (Conditions Met)' : 'FALSE (Conditions Not Met)'];
            $summary[] = ['label' => 'Logic Summary', 'value' => $output['summary'] ?? 'N/A'];
            
            // Handle Rule Groups (Detailed)
            if (!empty($output['groups'])) {
                foreach ($output['groups'] as $g) {
                    $gMatch = $g['match'] ? 'MATCH' : 'FAIL';
                    $gIndex = $g['index'] + 1;
                    
                    if (!$g['match']) {
                        foreach ($g['rules'] as $r) {
                            if (!$r['match']) {
                                $op = str_replace('_', ' ', $r['operator']);
                                $actual = is_null($r['actual']) ? 'NULL' : (is_bool($r['actual']) ? ($r['actual'] ? 'true' : 'false') : $r['actual']);
                                $summary[] = [
                                    'label' => "Group {$gIndex} Failure", 
                                    'value' => "Rule: '{$r['field']}' (Value: '{$actual}') did not match criteria: {$op} '{$r['expected']}'"
                                ];
                            }
                        }
                    }
                }
            } elseif (!empty($output['rules'])) {
                // Legacy / Simple rule list
                foreach ($output['rules'] as $rule) {
                    if (!$rule['match']) {
                        $actual = is_null($rule['actual']) ? 'NULL' : $rule['actual'];
                        $summary[] = [
                            'label' => 'Rule Failure', 
                            'value' => "Field '{$rule['field']}' ('{$actual}') failed '{$rule['operator']}' check."
                        ];
                    }
                }
            }
        }

        if ($step->node_type === 'wait') {
            $config = $node->config ?? [];
            $val = $config['delay_value'] ?? 0;
            $unit = $config['delay_unit'] ?? 'seconds';
            $summary[] = ['label' => 'Configured Delay', 'value' => "{$val} {$unit}"];
            $summary[] = ['label' => 'Simulation', 'value' => 'Skipped instantly during test'];
        }

        return $summary;
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        $steps = $this->session 
            ? $this->session->steps()->orderBy('order_index', 'desc')->get() 
            : collect();
            
        $activeStep = $this->activeStepId 
            ? SimulationStep::find($this->activeStepId) 
            : $steps->first();

        $breakpoints = SimulationBreakpoint::where('automation_flow_id', $this->automation->id)
            ->pluck('node_id')
            ->toArray();

        $history = SimulationSession::where('automation_flow_id', $this->automation->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.web.automations.simulation', [
            'steps' => $steps,
            'activeStep' => $activeStep,
            'breakpoints' => $breakpoints,
            'history' => $history,
        ]);
    }
}
