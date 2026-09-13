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
    public static array $entityRequirements = [
        'Sole proprietorship' => [
            ['name' => 'Business registration / licence evidence', 'description' => 'Official evidence showing that the business exists and is registered or licensed where applicable.', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Business address evidence', 'description' => 'An official or reliable document that supports the business address entered above.', 'is_required' => true, 'sort_order' => 2],
        ],
        'Partnership' => [
            ['name' => 'Partnership / registration evidence', 'description' => 'Official evidence establishing the partnership and its business registration where applicable.', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Business address evidence', 'description' => 'An official document supporting the business address.', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'Partner / authorization evidence', 'description' => 'Use when needed to show the submitting person is authorized to act for the partnership.', 'is_required' => true, 'sort_order' => 3],
        ],
        'LLP' => [
            ['name' => 'LLP registration / incorporation evidence', 'description' => 'Official evidence showing the LLP\'s registration and legal identity.', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Business address evidence', 'description' => 'An official document supporting the registered or business address.', 'is_required' => true, 'sort_order' => 2],
        ],
        'Private / public company' => [
            ['name' => 'Company incorporation / registration evidence', 'description' => 'Official evidence showing the company\'s registration and legal identity.', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Business address evidence', 'description' => 'An official document supporting the registered or business address.', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'Authorization evidence', 'description' => 'Use when needed to show the submitting person is authorized to act for the company.', 'is_required' => true, 'sort_order' => 3],
        ],
        'Trust / society / NGO' => [
            ['name' => 'Organization registration evidence', 'description' => 'Official evidence establishing the trust, society, NGO or other eligible organization.', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Organization address evidence', 'description' => 'An official document supporting the organization\'s address.', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'Authorization evidence', 'description' => 'Use when needed to show the submitting person is authorized to act for the organization.', 'is_required' => true, 'sort_order' => 3],
        ],
    ];

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

        if ($verification->business_type) {
            $this->syncChecklistForEntity($verification, $verification->business_type);
        } else {
            $this->syncChecklist($verification);
        }

        return $verification;
    }

    /**
     * Synchronize checklist based on selected business entity type.
     */
    public function syncChecklistForEntity(CompanyVerification $verification, string $businessType): void
    {
        $template = VerificationTemplate::firstOrCreate(
            ['country_code' => null, 'is_active' => true],
            [
                'name' => 'Standard Business Verification Template',
                'description' => 'Predefined verification requirements based on business entity type.',
                'sort_order' => 1,
            ]
        );

        $reqs = self::$entityRequirements[$businessType] ?? self::$entityRequirements['Sole proprietorship'];
        
        // Add optional document
        $allReqs = array_merge($reqs, [
            [
                'name' => 'Additional supporting document',
                'description' => 'Optional. Add another official document if it helps clarify your business information.',
                'is_required' => false,
                'sort_order' => 99,
            ]
        ]);

        $activeDocTypeIds = [];

        foreach ($allReqs as $req) {
            $docType = DocumentType::firstOrCreate(
                [
                    'verification_template_id' => $template->id,
                    'name' => $req['name'],
                ],
                [
                    'description' => $req['description'],
                    'accepted_formats' => 'pdf,jpg,png,jpeg',
                    'max_size_mb' => 10,
                    'is_required' => $req['is_required'],
                    'sort_order' => $req['sort_order'],
                    'is_active' => true,
                    'input_type' => 'document',
                ]
            );

            $activeDocTypeIds[] = $docType->id;

            CompanyVerificationDocument::firstOrCreate([
                'company_verification_id' => $verification->id,
                'document_type_id' => $docType->id,
            ]);
        }

        // Clean up documents belonging to previous entity choices if they have no uploads yet
        $existingDocs = CompanyVerificationDocument::where('company_verification_id', $verification->id)->get();
        foreach ($existingDocs as $doc) {
            if (!in_array($doc->document_type_id, $activeDocTypeIds) && $doc->status === 'not_submitted' && !$doc->latestVersion) {
                $doc->delete();
            }
        }

        $this->recalculateStatus($verification);
    }

    /**
     * Synchronize the checklist with the active checklist template for the company's country.
     */
    public function syncChecklist(CompanyVerification $verification): void
    {
        if ($verification->business_type) {
            $this->syncChecklistForEntity($verification, $verification->business_type);
            return;
        }

        // Try country-specific template first
        $template = VerificationTemplate::where('country_code', $verification->company->country ?? null)
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
        $verificationDoc = CompanyVerificationDocument::firstOrCreate([
            'company_verification_id' => $verification->id,
            'document_type_id' => $documentType->id,
        ]);

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
     * Submit a text input value for a requirement.
     */
    public function submitTextInput(
        CompanyVerification $verification,
        DocumentType $documentType,
        string $textValue,
        User $uploader
    ): CompanyVerificationDocumentVersion {
        $verificationDoc = CompanyVerificationDocument::where('company_verification_id', $verification->id)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        // Determine next version number
        $latestVersion = $verificationDoc->latestVersion;
        $nextVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        // Create new version for text input
        $version = CompanyVerificationDocumentVersion::create([
            'company_verification_document_id' => $verificationDoc->id,
            'version_number' => $nextVersionNumber,
            'file_path' => 'text_input',
            'file_name' => Str::limit($textValue, 50),
            'mime_type' => 'text/plain',
            'file_size' => strlen($textValue),
            'text_value' => $textValue,
            'status' => 'pending_review',
            'uploaded_by' => $uploader->id,
        ]);

        // Update document state
        $verificationDoc->update(['status' => 'pending_review']);

        // Log timeline event
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'upload',
            'title' => 'Information Submitted',
            'description' => "Submitted Version {$nextVersionNumber} of {$documentType->name}.",
            'actor_id' => $uploader->id,
            'metadata' => ['document_name' => $documentType->name, 'version' => $nextVersionNumber, 'type' => 'input'],
        ]);

        // Log audit trail
        VerificationAuditLog::create([
            'company_id' => $verification->company_id,
            'user_id' => $uploader->id,
            'action' => 'submit_text_input',
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
