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

    // Wizard Step state (1: Entity, 2: Details, 3: Documents, 4: Review, 5: Submitted Timeline)
    public int $currentStep = 1;

    // Step 1 & 2 Form State
    public ?string $business_type = null;
    public ?string $legal_name = null;
    public ?string $display_name = null;
    public ?string $category = null;
    public ?string $website = null;
    public ?string $address = null;
    public ?string $signatory_name = null;
    public ?string $signatory_designation = null;
    public ?string $business_email = null;
    public ?string $business_phone = null;
    public ?string $wa_phone = null;

    // Step 3 Document Upload Modals & Upload State
    public $showUploadModal = false;
    public $showHistoryModal = false;
    public $selectedDocTypeId = null;
    public $file = null;
    public $issueDate = null;
    public $expiryDate = null;

    // History Modal State
    public $selectedDocTypeForHistory = null;
    public $historyDocumentsList = [];

    // Step 4 Declaration
    public bool $confirmDeclaration = false;

    protected $listeners = ['refreshVerification' => '$refresh'];

    public function mount(VerificationWorkflowService $service)
    {
        $company = Auth::user()->company;
        if ($company) {
            $verification = $service->getOrCreateVerification($company);

            // Populate form fields if already present
            $this->business_type = $verification->business_type;
            $this->legal_name = $verification->legal_name ?? $company->name;
            $this->display_name = $verification->display_name ?? $company->name;
            $this->category = $verification->category;
            $this->website = $verification->website ?? $company->website_url;
            $this->address = $verification->address;
            $this->signatory_name = $verification->signatory_name;
            $this->signatory_designation = $verification->signatory_designation;
            $this->business_email = $verification->business_email ?? $company->primary_email;
            $this->business_phone = $verification->business_phone;
            $this->wa_phone = $verification->wa_phone;

            // If verification is already submitted / under review / verified / rejected, default to step 5 (tracking screen)
            if ($verification->submitted_at || in_array($verification->status, ['under_review', 'partially_approved', 'verified', 'rejected', 'suspended'])) {
                $this->currentStep = 5;
            } elseif ($this->business_type) {
                // If entity is already selected, go to step 2
                $this->currentStep = 2;
            }
        }
    }

    public function selectEntityType(string $entity, VerificationWorkflowService $service)
    {
        $this->business_type = $entity;
        $company = Auth::user()->company;
        $verification = $service->getOrCreateVerification($company);

        $verification->update([
            'business_type' => $entity,
        ]);

        $service->syncChecklistForEntity($verification, $entity);
    }

    public function goStep(int $step, VerificationWorkflowService $service = null)
    {
        if ($step === 2 && !$this->business_type) {
            $this->addError('business_type', 'Please select a business entity type before continuing.');
            return;
        }

        if ($step >= 3 && $this->currentStep < 5) {
            $this->validate([
                'legal_name' => 'required|string|max:255',
                'display_name' => 'required|string|max:255',
                'category' => 'required|string',
                'address' => 'required|string',
                'signatory_name' => 'required|string|max:255',
                'signatory_designation' => 'required|string|max:255',
                'business_email' => 'required|email|max:255',
                'business_phone' => 'required|string|max:50',
                'wa_phone' => 'required|string|max:50',
                'website' => 'nullable|url|max:255',
            ]);

            // Save details to verification model
            $company = Auth::user()->company;
            if ($company) {
                $verification = CompanyVerification::where('company_id', $company->id)->first();
                if ($verification) {
                    $verification->update([
                        'business_type' => $this->business_type,
                        'legal_name' => $this->legal_name,
                        'display_name' => $this->display_name,
                        'category' => $this->category,
                        'website' => $this->website,
                        'address' => $this->address,
                        'signatory_name' => $this->signatory_name,
                        'signatory_designation' => $this->signatory_designation,
                        'business_email' => $this->business_email,
                        'business_phone' => $this->business_phone,
                        'wa_phone' => $this->wa_phone,
                    ]);

                    if ($service && $this->business_type) {
                        $service->syncChecklistForEntity($verification, $this->business_type);
                    }
                }
            }
        }

        $this->currentStep = $step;
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

        $maxSizeKb = $docType->max_size_mb * 1024;
        $formats = $docType->accepted_formats ?: 'pdf,jpg,png,jpeg';

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

    public function submitApplication(VerificationWorkflowService $workflowService)
    {
        if (!$this->confirmDeclaration) {
            $this->addError('confirmDeclaration', 'You must confirm the declaration before submitting.');
            return;
        }

        $company = Auth::user()->company;
        $verification = $workflowService->getOrCreateVerification($company);

        // Update verification status & timestamp
        $verification->update([
            'status' => 'under_review',
            'submitted_at' => now(),
            'last_activity_at' => now(),
        ]);

        // Add timeline event
        \App\Models\CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'status_change',
            'title' => 'Application Submitted',
            'description' => 'Your business verification application was received successfully and is under internal review.',
            'actor_id' => Auth::id(),
            'metadata' => ['status' => 'under_review'],
        ]);

        // Add audit trail
        \App\Models\VerificationAuditLog::create([
            'company_id' => $company->id,
            'user_id' => Auth::id(),
            'action' => 'submit_verification_application',
        ]);

        $workflowService->recalculateStatus($verification);

        session()->flash('success', 'Your verification application has been submitted successfully.');
        $this->currentStep = 5;
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
            'title' => 'Business Verification — WhatsApp Cloud Platform',
            'activeNav' => 'company-verification',
        ]);
    }
}
