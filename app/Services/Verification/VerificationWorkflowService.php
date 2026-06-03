<?php

namespace App\Services\Verification;

use App\Models\Company;
use App\Models\User;
use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\CompanyVerificationDocumentVersion;
use App\Models\CompanyVerificationTimeline;
use App\Models\VerificationAuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationWorkflowService
{
    /**
     * Get or create verification state for a company.
     */
    public function getOrCreateVerification(Company $company): CompanyVerification
    {
        $verification = CompanyVerification::firstOrCreate(
            ['company_id' => $company->id],
            [
                'status' => 'not_started',
                'progress_percentage' => 0,
            ]
        );

        $this->syncChecklist($verification);

        return $verification;
    }

    /**
     * Synchronize the checklist with the active checklist template for the company's country.
     */
    public function syncChecklist(CompanyVerification $verification): void
    {
        $company = $verification->company;

        // Try country-specific template first
        $template = VerificationTemplate::where('country_code', $company->country)
            ->where('is_active', true)
            ->first();

        // Fallback to global template (country_code is null)
        if (!$template) {
            $template = VerificationTemplate::whereNull('country_code')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        }

        if (!$template) {
            return;
        }

        // Get all active document types for the template
        $documentTypes = $template->documentTypes()->where('is_active', true)->get();

        foreach ($documentTypes as $docType) {
            CompanyVerificationDocument::firstOrCreate([
                'company_verification_id' => $verification->id,
                'document_type_id' => $docType->id,
            ]);
        }

        $this->recalculateStatus($verification);
    }

    /**
     * Upload a new document version.
     */
    public function uploadDocument(
        CompanyVerification $verification,
        DocumentType $documentType,
        UploadedFile $file,
        User $uploader,
        ?string $issueDate = null,
        ?string $expiryDate = null
    ): CompanyVerificationDocumentVersion {
        $verificationDoc = CompanyVerificationDocument::where('company_verification_id', $verification->id)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        // Get file properties before storing
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Store file securely in private disk
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('verification-docs/' . $verification->company_id, $filename, 'local');

        // Determine next version number
        $latestVersion = $verificationDoc->latestVersion;
        $nextVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        // Create new version
        $version = CompanyVerificationDocumentVersion::create([
            'company_verification_document_id' => $verificationDoc->id,
            'version_number' => $nextVersionNumber,
            'file_path' => $filePath,
            'file_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'status' => 'pending_review',
            'uploaded_by' => $uploader->id,
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
        ]);

        // Update document state
        $verificationDoc->update(['status' => 'pending_review']);

        // Log timeline event
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'upload',
            'title' => 'Document Uploaded',
            'description' => "Uploaded Version {$nextVersionNumber} of {$documentType->name}.",
            'actor_id' => $uploader->id,
            'metadata' => ['document_name' => $documentType->name, 'version' => $nextVersionNumber],
        ]);

        // Log audit trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => $uploader->id,
            'action' => 'upload_document',
            'metadata' => ['document_type_id' => $documentType->id, 'version_id' => $version->id],
        ]);

        $this->recalculateStatus($verification);

        return $version;
    }

    /**
     * Approve document version.
     */
    public function approveDocument(CompanyVerificationDocumentVersion $version, User $reviewer, ?string $notes = null): void
    {
        $version->update([
            'status' => 'approved',
            'reviewer_notes' => $notes,
        ]);

        $verificationDoc = $version->document;
        $verificationDoc->update(['status' => 'approved']);

        $verification = $verificationDoc->verification;

        // Log timeline
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'approve_doc',
            'title' => 'Document Approved',
            'description' => "Approved {$verificationDoc->documentType->name}.",
            'actor_id' => $reviewer->id,
            'metadata' => ['document_name' => $verificationDoc->documentType->name],
        ]);

        // Audit log
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => $reviewer->id,
            'action' => 'approve_document',
            'metadata' => ['document_type_id' => $verificationDoc->document_type_id, 'version_id' => $version->id],
        ]);

        $this->recalculateStatus($verification);
    }

    /**
     * Reject document version.
     */
    public function rejectDocument(CompanyVerificationDocumentVersion $version, User $reviewer, string $reasonCode, ?string $notes = null): void
    {
        $version->update([
            'status' => 'rejected',
            'rejection_reason' => $reasonCode,
            'reviewer_notes' => $notes,
        ]);

        $verificationDoc = $version->document;
        $verificationDoc->update(['status' => 'rejected']);

        $verification = $verificationDoc->verification;

        // Log timeline
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'reject_doc',
            'title' => 'Document Rejected',
            'description' => "Rejected {$verificationDoc->documentType->name}. Reason: {$reasonCode}.",
            'actor_id' => $reviewer->id,
            'metadata' => ['document_name' => $verificationDoc->documentType->name, 'reason' => $reasonCode],
        ]);

        // Audit log
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => $reviewer->id,
            'action' => 'reject_document',
            'metadata' => ['document_type_id' => $verificationDoc->document_type_id, 'version_id' => $version->id, 'reason' => $reasonCode],
        ]);

        $this->recalculateStatus($verification);
    }

    /**
     * Recalculate status and completion percentage of the company verification.
     */
    public function recalculateStatus(CompanyVerification $verification): void
    {
        $verification->load(['documents.documentType', 'documents.latestVersion']);
        $docs = $verification->documents;

        if ($docs->isEmpty()) {
            $verification->update([
                'status' => 'not_started',
                'progress_percentage' => 0,
            ]);
            return;
        }

        $totalRequired = 0;
        $approvedRequired = 0;
        $hasPending = false;
        $hasRejected = false;
        $hasUploaded = false;
        $hasExpired = false;

        foreach ($docs as $doc) {
            $isReq = (bool) $doc->documentType->is_required;
            if ($isReq) {
                $totalRequired++;
            }

            $latest = $doc->latestVersion;

            if ($latest) {
                $hasUploaded = true;

                if ($latest->status === 'approved') {
                    if ($isReq) {
                        $approvedRequired++;
                    }
                    // Expiry check
                    if ($latest->expiry_date && $latest->expiry_date->isPast()) {
                        $hasExpired = true;
                    }
                } elseif ($latest->status === 'rejected') {
                    $hasRejected = true;
                } else {
                    $hasPending = true;
                }
            }
        }

        // Calculate progress percentage
        $progress = $totalRequired > 0 
            ? (int) round(($approvedRequired / $totalRequired) * 100) 
            : 0;

        // Determine Status
        $oldStatus = $verification->status;
        $status = 'not_started';

        if ($oldStatus === 'suspended') {
            // Admin manual intervention suspension is preserved
            $status = 'suspended';
        } elseif ($hasExpired) {
            $status = 'expired';
        } elseif ($hasRejected) {
            $status = 'rejected';
        } elseif ($totalRequired > 0 && $approvedRequired === $totalRequired) {
            $status = 'verified';
        } elseif ($hasPending) {
            if ($approvedRequired > 0) {
                $status = 'partially_approved';
            } else {
                $status = 'under_review';
            }
        } elseif ($hasUploaded) {
            $status = 'in_progress';
        }

        $verification->update([
            'status' => $status,
            'progress_percentage' => $progress,
            'last_activity_at' => now(),
        ]);

        // Log status change timeline
        if ($oldStatus !== $status) {
            CompanyVerificationTimeline::create([
                'company_verification_id' => $verification->id,
                'event_type' => 'status_change',
                'title' => 'Verification Status Updated',
                'description' => "Status changed from " . ucwords(str_replace('_', ' ', $oldStatus)) . " to " . ucwords(str_replace('_', ' ', $status)) . ".",
                'metadata' => ['old_status' => $oldStatus, 'new_status' => $status],
            ]);
        }
    }
}
