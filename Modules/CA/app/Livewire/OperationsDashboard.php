<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Services\ComplianceDashboardService;
use Illuminate\Support\Facades\Auth;

class OperationsDashboard extends Component
{
    public array $metrics = [];

    public function mount(ComplianceDashboardService $dashboardService)
    {
        $companyId = Auth::user()->company_id;
        $this->metrics = $dashboardService->getDashboardMetrics($companyId);
    }

    public function render()
    {
        return view('ca::livewire.operations-dashboard')->layout('layouts.panel');
    }
}
