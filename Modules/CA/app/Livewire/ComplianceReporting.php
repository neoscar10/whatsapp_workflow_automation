<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CAClientCompliance;
use Illuminate\Support\Facades\Auth;

class ComplianceReporting extends Component
{
    public string $filterStatus = '';

    public function render()
    {
        $companyId = Auth::user()->company_id;
        
        $query = CAClientCompliance::with(['client', 'compliance'])
            ->whereHas('client', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        if ($this->filterStatus) {
            $query->where('health_status', $this->filterStatus);
        }

        $compliances = $query->paginate(20);

        return view('ca::livewire.compliance-reporting', [
            'compliances' => $compliances
        ])->layout('layouts.panel');
    }
}
