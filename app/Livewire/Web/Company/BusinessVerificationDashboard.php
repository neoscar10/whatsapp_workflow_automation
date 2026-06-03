<?php

namespace App\Livewire\Web\Company;

use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\DocumentType;
use App\Services\Verification\VerificationWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessVerificationDashboard extends Component
{
    use WithFileUploads;

    // Modals
    public $showUploadModal = false;
    public $showHistoryModal = false;

    // Upload Form State
    public $selectedDocTypeId = null;
    public $file = null;
    public $issueDate = null;
    public $expiryDate = null;

    // History Modal State
    public $selectedDocTypeForHistory = null;
    public $historyDocumentsList = [];

    protected $listeners = ['refreshVerification' => '$refresh'];

    public function mount(VerificationWorkflowService $service)
    {
        $company = Auth::user()->company;
        if ($company) {
            $service->getOrCreateVerification($company);
        }
    }

    public function openUploadModal($docTypeId)
    {
        $this->resetUploadForm();
        $this->selectedDocTypeId = $docTypeId;
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->resetUploadForm();
    }

    public function openHistoryModal($docTypeId)
    {
        $this->selectedDocTypeForHistory = DocumentType::findOrFail($docTypeId);
        $company = Auth::user()->company;
        $verification = CompanyVerification::where('company_id', $company->id)->first();
        
        if ($verification) {
            $compDoc = CompanyVerificationDocument::where('company_verification_id', $verification->id)
                ->where('document_type_id', $docTypeId)
                ->first();
                
            $this->historyDocumentsList = $compDoc 
                ? $compDoc->versions()->with('uploader')->get() 
                : [];
        } else {
            $this->historyDocumentsList = [];
        }

        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedDocTypeForHistory = null;
        $this->historyDocumentsList = [];
    }

    public function submitDocument(VerificationWorkflowService $workflowService)
    {
        $docType = DocumentType::findOrFail($this->selectedDocTypeId);
        $company = Auth::user()->company;
        $verification = $workflowService->getOrCreateVerification($company);

        // Validation Rules dynamically matching document requirements
        $maxSizeKb = $docType->max_size_mb * 1024;
        $formats = $docType->accepted_formats; // e.g. 'pdf,jpg,png,jpeg'

        $this->validate([
            'file' => "required|file|max:{$maxSizeKb}|mimes:{$formats}",
            'issueDate' => 'nullable|date',
            'expiryDate' => 'nullable|date|after_or_equal:issueDate',
        ]);

        $workflowService->uploadDocument(
            $verification,
            $docType,
            $this->file,
            Auth::user(),
            $this->issueDate,
            $this->expiryDate
        );

        session()->flash('success', "{$docType->name} uploaded successfully and is pending review.");

        $this->closeUploadModal();
        $this->dispatch('refreshVerification');
    }

    private function resetUploadForm()
    {
        $this->selectedDocTypeId = null;
        $this->file = null;
        $this->issueDate = null;
        $this->expiryDate = null;
        $this->resetErrorBag();
    }

    public function render(VerificationWorkflowService $service)
    {
        $company = Auth::user()->company;
        $verification = $service->getOrCreateVerification($company);
        
        // Load documents with type and versions
        $verification->load([
            'documents.documentType',
            'documents.latestVersion',
            'timeline.actor'
        ]);

        $approvedCount = 0;
        $pendingCount = 0;
        $rejectedCount = 0;

        foreach ($verification->documents as $doc) {
            $latest = $doc->latestVersion;
            if ($latest) {
                if ($latest->status === 'approved') {
                    $approvedCount++;
                } elseif ($latest->status === 'rejected') {
                    $rejectedCount++;
                } else {
                    $pendingCount++;
                }
            }
        }

        return view('livewire.web.company.business-verification-dashboard', [
            'verification' => $verification,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'rejectedCount' => $rejectedCount,
        ])
        ->layout('layouts.panel', [
            'title' => 'Business Verification - WhatsApp Cloud Panel',
            'activeNav' => 'company-profile',
        ]);
    }
}
