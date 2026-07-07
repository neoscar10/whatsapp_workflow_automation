<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CADocument;
use Illuminate\Support\Facades\Auth;

class ComplianceDocumentHistory extends Component
{
    use WithPagination;

    public $client;
    public $clientCompliance;
    public $search = '';
    public $statusFilter = '';
    public $requirementFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'requirementFilter' => ['except' => ''],
    ];

    public function mount($clientId, $clientComplianceId)
    {
        $this->client = CAClient::where('company_id', Auth::user()->company_id)
            ->findOrFail($clientId);

        $this->clientCompliance = CAClientCompliance::with(['compliance', 'clientRequirements'])
            ->where('ca_client_id', $clientId)
            ->findOrFail($clientComplianceId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingRequirementFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = CADocument::where('ca_client_compliance_id', $this->clientCompliance->id)
            ->with(['clientComplianceRequirement', 'uploadedBy'])
            ->orderByDesc('created_at');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('document_name', 'like', '%' . $this->search . '%')
                  ->orWhere('original_filename', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->requirementFilter)) {
            $query->where('ca_client_compliance_requirement_id', $this->requirementFilter);
        }

        $documents = $query->paginate(15);

        return view('ca::livewire.compliance-document-history', [
            'documents' => $documents,
            'requirements' => $this->clientCompliance->clientRequirements,
        ])->layout('layouts.panel');
    }
}
