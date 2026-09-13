<?php

namespace App\Livewire\SuperAdmin;

use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\CompanyVerificationDocumentVersion;
use App\Models\CompanyVerificationTimeline;
use App\Models\VerificationAuditLog;
use App\Services\Verification\VerificationWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VerificationReviewWorkspace extends Component
{
    public $verificationId;
    
    // Focused document
    public $selectedDocId = null;

    // Review Actions Form
    public $showActionModal = false;
    public $showRejectionDialog = false;
    public $activeVersionIdForRejection = null;
    public $rejectionReason = 'document_unclear';
    public $reviewerNotes = '';

    public function mount($id, VerificationWorkflowService $workflowService)
    {
        $this->verificationId = $id;
        
        $verification = CompanyVerification::findOrFail($id);

        if ($verification->business_type) {
            $workflowService->syncChecklistForEntity($verification, $verification->business_type);
        }

        $firstDoc = $verification->documents()->first();
        if ($firstDoc) {
            $this->selectedDocId = $firstDoc->id;
        }
    }

    public function selectDocument($docId)
    {
        $this->selectedDocId = $docId;
        $this->showActionModal = false;
        $this->resetRejectionForm();
    }

    public function approve(VerificationWorkflowService $workflowService, $versionId)
    {
        $version = CompanyVerificationDocumentVersion::findOrFail($versionId);
        $workflowService->approveDocument($version, Auth::user(), $this->reviewerNotes);
        
        $this->reviewerNotes = '';
        $this->showActionModal = false;
        session()->flash('success_review', "Document approved successfully.");
    }

    public function openRejectionDialog($versionId)
    {
        $this->activeVersionIdForRejection = $versionId;
        $this->showRejectionDialog = true;
    }

    public function closeRejectionDialog()
    {
        $this->showRejectionDialog = false;
        $this->showActionModal = false;
        $this->resetRejectionForm();
    }

    public function reject(VerificationWorkflowService $workflowService)
    {
        $this->validate([
            'rejectionReason' => 'required|string',
            'reviewerNotes' => 'nullable|string|max:1000',
        ]);

        $version = CompanyVerificationDocumentVersion::findOrFail($this->activeVersionIdForRejection);
        $workflowService->rejectDocument($version, Auth::user(), $this->rejectionReason, $this->reviewerNotes);

        session()->flash('success_review', "Document rejected successfully.");
        $this->closeRejectionDialog();
    }

    public function approveVerification(VerificationWorkflowService $workflowService)
    {
        $verification = CompanyVerification::findOrFail($this->verificationId);
        $oldStatus = $verification->status;

        $verification->update([
            'status' => 'verified',
            'progress_percentage' => 100,
            'last_activity_at' => now(),
        ]);

        // Log timeline
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'status_change',
            'title' => 'Verification Fully Approved',
            'description' => 'Super admin manually approved the company verification application.',
            'actor_id' => Auth::id(),
            'metadata' => ['old_status' => $oldStatus, 'new_status' => 'verified'],
        ]);

        // Audit Trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => Auth::id(),
            'action' => 'approve_verification_application',
        ]);

        session()->flash('success_review', "Verification application approved successfully!");
        $this->dispatch('$refresh');
    }

    public function rejectVerification(VerificationWorkflowService $workflowService)
    {
        $verification = CompanyVerification::findOrFail($this->verificationId);
        $oldStatus = $verification->status;

        $verification->update([
            'status' => 'rejected',
            'last_activity_at' => now(),
        ]);

        // Log timeline
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'status_change',
            'title' => 'Verification Rejected',
            'description' => 'Super admin marked the verification application as requiring changes / rejected.',
            'actor_id' => Auth::id(),
            'metadata' => ['old_status' => $oldStatus, 'new_status' => 'rejected'],
        ]);

        // Audit Trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => Auth::id(),
            'action' => 'reject_verification_application',
        ]);

        session()->flash('success_review', "Verification application marked as rejected / changes requested.");
        $this->dispatch('$refresh');
    }

    public function suspendVerification(VerificationWorkflowService $workflowService)
    {
        $verification = CompanyVerification::findOrFail($this->verificationId);
        $oldStatus = $verification->status;
        
        $verification->update(['status' => 'suspended']);

        // Log timeline
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'status_change',
            'title' => 'Verification Suspended',
            'description' => "Verification suspended manually by super admin.",
            'actor_id' => Auth::id(),
            'metadata' => ['old_status' => $oldStatus, 'new_status' => 'suspended'],
        ]);

        // Audit Trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => Auth::id(),
            'action' => 'suspend_verification',
        ]);

        session()->flash('success_review', "Verification status suspended.");
        $this->dispatch('$refresh');
    }

    public function unsuspendVerification(VerificationWorkflowService $workflowService)
    {
        $verification = CompanyVerification::findOrFail($this->verificationId);
        
        $verification->update(['status' => 'under_review']);
        $workflowService->recalculateStatus($verification);

        // Audit Trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => Auth::id(),
            'action' => 'unsuspend_verification',
        ]);

        session()->flash('success_review', "Verification suspension lifted.");
        $this->dispatch('$refresh');
    }

    private function resetRejectionForm()
    {
        $this->activeVersionIdForRejection = null;
        $this->rejectionReason = 'document_unclear';
        $this->reviewerNotes = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $verification = CompanyVerification::with([
            'company', 
            'documents.documentType', 
            'documents.versions.uploader',
            'timeline.actor'
        ])->findOrFail($this->verificationId);

        $focusedDoc = null;
        $previewUrl = null;
        $fileMime = null;

        if ($this->selectedDocId) {
            $focusedDoc = CompanyVerificationDocument::with(['documentType', 'versions.uploader', 'latestVersion'])
                ->find($this->selectedDocId);
                
            if ($focusedDoc) {
                $latest = $focusedDoc->latestVersion;
                if ($latest) {
                    $previewUrl = $latest->getDownloadUrl();
                    $fileMime = $latest->mime_type;
                }
            }
        }

        return view('livewire.super-admin.verification-review-workspace', [
            'verification' => $verification,
            'focusedDoc' => $focusedDoc,
            'previewUrl' => $previewUrl,
            'fileMime' => $fileMime,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Company Verification Workspace',
            'activeNav' => 'verification-queue',
        ]);
    }
}
