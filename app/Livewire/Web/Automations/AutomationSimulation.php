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
    public $initialPayload = '{\n  "contact": {\n    "name": "John Doe",\n    "phone": "1234567890"\n  },\n  "trigger_source": "manual_test"\n}';
    
    public function mount(int $id)
    {
        $this->automation = AutomationFlow::where('company_id', auth()->user()->company_id)
            ->findOrFail($id);
            
        $this->loadFlowData();
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
