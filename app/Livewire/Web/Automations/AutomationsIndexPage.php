<?php

namespace App\Livewire\Web\Automations;

use App\Models\AutomationFlow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Automations')]
class AutomationsIndexPage extends Component
{
    use WithPagination;

    public string $filter = 'all';
    public ?int $automationToDelete = null;

    public function setFilter(string $status)
    {
        $this->filter = $status;
        $this->resetPage();
    }

    public function toggleStatus(int $id)
    {
        $automation = AutomationFlow::where('company_id', auth()->user()->company_id)->findOrFail($id);
        
        if ($automation->status === 'active') {
            $automation->status = 'paused';
            $automation->is_enabled = false;
        } elseif ($automation->status === 'paused' || $automation->status === 'draft') {
            // draft to active transition might need validation in real app, but following requested behavior
            $automation->status = 'active';
            $automation->is_enabled = true;
        }
        
        $automation->save();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Automation status updated']);
    }

    public function duplicate(int $id)
    {
        $original = AutomationFlow::where('company_id', auth()->user()->company_id)->findOrFail($id);
        
        $copy = $original->replicate();
        $copy->name = $original->name . ' (Copy)';
        $copy->status = 'draft';
        $copy->is_enabled = false;
        $copy->total_executions = 0;
        $copy->last_run_at = null;
        $copy->created_by_user_id = auth()->id();
        $copy->save();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Automation duplicated successfully']);
    }

    public function confirmDelete(int $id)
    {
        $this->automationToDelete = $id;
    }

    public function deleteAutomation()
    {
        if (!$this->automationToDelete) return;

        $automation = AutomationFlow::where('company_id', auth()->user()->company_id)->findOrFail($this->automationToDelete);
        $automation->delete();

        $this->automationToDelete = null;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Automation deleted']);
    }

    #[Layout('layouts.panel')]
    public function render()
    {
        $query = AutomationFlow::where('company_id', auth()->user()->company_id)
            ->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $automations = $query->paginate(9);

        // Counts for tabs
        $counts = [
            'all' => AutomationFlow::where('company_id', auth()->user()->company_id)->count(),
            'active' => AutomationFlow::where('company_id', auth()->user()->company_id)->where('status', 'active')->count(),
            'draft' => AutomationFlow::where('company_id', auth()->user()->company_id)->where('status', 'draft')->count(),
            'paused' => AutomationFlow::where('company_id', auth()->user()->company_id)->where('status', 'paused')->count(),
        ];

        return view('livewire.web.automations.index-page', [
            'automations' => $automations,
            'counts' => $counts,
        ])->layoutData([
            'activeNav' => 'automations',
        ]);
    }
}
