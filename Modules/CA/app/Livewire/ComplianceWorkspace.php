<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CADocument;
use Modules\CA\Services\DocumentService;
use Modules\CA\Services\ReviewService;
use Illuminate\Support\Facades\Auth;

class ComplianceWorkspace extends Component
{
    use WithFileUploads;

    public $client;
    public $clientCompliance;
    
    // Upload state tracking
    public $uploads = []; // requirement_id => uploaded_file

    public function mount($clientId, $clientComplianceId)
    {
        $this->client = CAClient::where('company_id', Auth::user()->company_id)
            ->findOrFail($clientId);
            
        $this->clientCompliance = CAClientCompliance::with([
            'compliance',
            'clientRequirements.complianceRequirement',
            'deadlines',
            'documents'
        ])
        ->where('ca_client_id', $clientId)
        ->findOrFail($clientComplianceId);
    }

    public function uploadDocument($requirementId)
    {
        $this->validate([
            'uploads.' . $requirementId => 'required|file|max:10240', // 10MB default
        ]);

        $file = $this->uploads[$requirementId];
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);

        $documentService = app(DocumentService::class);
        
        $document = $documentService->storeDocument($file, Auth::user(), [
            'ca_client_id' => $this->client->id,
            'ca_client_compliance_id' => $this->clientCompliance->id,
            'ca_client_compliance_requirement_id' => $requirement->id,
            'document_name' => clone $requirement->name,
        ]);

        $requirement->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        session()->flash('message', 'Document uploaded successfully!');
        
        // Reset the upload input
        unset($this->uploads[$requirementId]);
        
        // Refresh component data
        $this->clientCompliance->refresh();
    }

    public function approveRequirement($requirementId)
    {
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);
        $document = CADocument::where('ca_client_compliance_requirement_id', $requirementId)->latest()->first();
        
        if ($document) {
            $reviewService = app(ReviewService::class);
            $reviewService->approveDocument($document, Auth::user());
        } else {
            // Text or boolean requirement, no doc to approve
            $requirement->update([
                'status' => 'approved',
                'is_completed' => true,
                'approved_at' => now(),
            ]);
        }

        $this->clientCompliance->refresh();
    }

    public function rejectRequirement($requirementId)
    {
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);
        $document = CADocument::where('ca_client_compliance_requirement_id', $requirementId)->latest()->first();
        
        if ($document) {
            $reviewService = app(ReviewService::class);
            $reviewService->rejectDocument($document, Auth::user(), "Rejected by reviewer.");
        } else {
            $requirement->update([
                'status' => 'rejected',
                'is_completed' => false,
            ]);
        }

        $this->clientCompliance->refresh();
    }

    public function render()
    {
        $total = $this->clientCompliance->clientRequirements->count();
        $completed = $this->clientCompliance->clientRequirements->where('is_completed', true)->count();
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

        return view('ca::livewire.compliance-workspace', [
            'progress' => $progress,
            'totalRequirements' => $total,
            'completedRequirements' => $completed
        ])->layout('layouts.panel');
    }
}
