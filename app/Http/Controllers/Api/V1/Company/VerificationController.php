<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Company\SelectEntityTypeRequest;
use App\Http\Requests\Api\V1\Company\UpdateVerificationDetailsRequest;
use App\Http\Requests\Api\V1\Company\UploadVerificationDocumentRequest;
use App\Http\Requests\Api\V1\Company\SubmitVerificationApplicationRequest;
use App\Http\Resources\Api\V1\Company\CompanyVerificationResource;
use App\Http\Resources\Api\V1\Company\DocumentTypeResource;
use App\Http\Resources\Api\V1\Company\VerificationDocumentVersionResource;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\CompanyVerificationTimeline;
use App\Models\DocumentType;
use App\Models\VerificationAuditLog;
use App\Services\Verification\VerificationWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    use RespondsWithApiResponse;

    protected VerificationWorkflowService $workflowService;

    public function __construct(VerificationWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Get the current verification status, required documents, and available entity types.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        
        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = $this->workflowService->getOrCreateVerification($company);
        
        $verification->load([
            'documents.documentType',
            'documents.latestVersion',
            'timeline.actor'
        ]);

        $availableEntities = [
            [
                'name' => 'Sole proprietorship',
                'code' => 'SP',
                'description' => 'A business owned and operated by one individual.',
            ],
            [
                'name' => 'Partnership',
                'code' => 'PF',
                'description' => 'A business operated by two or more partners.',
            ],
            [
                'name' => 'LLP',
                'code' => 'LL',
                'description' => 'A registered LLP with separate legal identity.',
            ],
            [
                'name' => 'Private / public company',
                'code' => 'CO',
                'description' => 'A company registered as a corporate entity.',
            ],
            [
                'name' => 'Trust / society / NGO',
                'code' => 'NG',
                'description' => 'A registered non-profit or other eligible organization.',
            ],
        ];

        $data = [
            'verification' => new CompanyVerificationResource($verification),
            'available_entity_types' => $availableEntities,
        ];

        return $this->successResponse($data, 'Verification status retrieved successfully.');
    }

    /**
     * Select business entity type (Step 1).
     */
    public function selectEntityType(SelectEntityTypeRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = $this->workflowService->getOrCreateVerification($company);
        $entityType = $request->business_type;

        $verification->update([
            'business_type' => $entityType,
            'last_activity_at' => now(),
        ]);

        $this->workflowService->syncChecklistForEntity($verification, $entityType);

        $verification->load([
            'documents.documentType',
            'documents.latestVersion',
            'timeline.actor'
        ]);

        return $this->successResponse(
            new CompanyVerificationResource($verification),
            "Business entity structure updated to {$entityType}."
        );
    }

    /**
     * Save/update detailed business information (Step 2).
     */
    public function updateDetails(UpdateVerificationDetailsRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = $this->workflowService->getOrCreateVerification($company);

        if (!$verification->business_type) {
            return $this->errorResponse('Please select a business entity type before providing business details.', [], 422);
        }

        $verification->update([
            'legal_name' => $request->legal_name,
            'display_name' => $request->display_name,
            'category' => $request->category,
            'website' => $request->website,
            'address' => $request->address,
            'signatory_name' => $request->signatory_name,
            'signatory_designation' => $request->signatory_designation,
            'business_email' => $request->business_email,
            'business_phone' => $request->business_phone,
            'wa_phone' => $request->wa_phone,
            'last_activity_at' => now(),
        ]);

        $this->workflowService->syncChecklistForEntity($verification, $verification->business_type);

        $verification->load([
            'documents.documentType',
            'documents.latestVersion',
            'timeline.actor'
        ]);

        return $this->successResponse(
            new CompanyVerificationResource($verification),
            'Business details saved successfully.'
        );
    }

    /**
     * Upload a new verification document (Step 3).
     */
    public function uploadDocument(UploadVerificationDocumentRequest $request): JsonResponse
    {
        $company = $request->user()->company;
        $user = $request->user();

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = $this->workflowService->getOrCreateVerification($company);
        $docType = DocumentType::findOrFail($request->document_type_id);

        try {
            $version = $this->workflowService->uploadDocument(
                $verification,
                $docType,
                $request->file('file'),
                $user,
                $request->issue_date,
                $request->expiry_date
            );

            return $this->successResponse(
                new VerificationDocumentVersionResource($version->load(['uploader'])),
                "{$docType->name} uploaded successfully and is pending review.",
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload document: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get the version history for a specific document type.
     */
    public function history(Request $request, string $documentTypeId): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = CompanyVerification::where('company_id', $company->id)->first();
        if (!$verification) {
            return $this->successResponse([], 'No history found.');
        }

        $compDoc = CompanyVerificationDocument::where('company_verification_id', $verification->id)
            ->where('document_type_id', $documentTypeId)
            ->first();

        if (!$compDoc) {
            return $this->successResponse([], 'No history found.');
        }

        $versions = $compDoc->versions()->with(['uploader', 'reviewer'])->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            VerificationDocumentVersionResource::collection($versions),
            'Document history retrieved successfully.'
        );
    }

    /**
     * Final submission of the verification application (Step 4).
     */
    public function submit(SubmitVerificationApplicationRequest $request): JsonResponse
    {
        $company = $request->user()->company;
        $user = $request->user();

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $verification = $this->workflowService->getOrCreateVerification($company);

        if (!$verification->business_type) {
            return $this->errorResponse('Please select a business entity type before submitting.', [], 422);
        }

        if (!$verification->legal_name || !$verification->address || !$verification->business_email || !$verification->wa_phone) {
            return $this->errorResponse('Please fill in all required business details before submitting.', [], 422);
        }

        // Update verification status & timestamp
        $verification->update([
            'status' => 'under_review',
            'submitted_at' => now(),
            'last_activity_at' => now(),
        ]);

        // Add timeline event
        CompanyVerificationTimeline::create([
            'company_verification_id' => $verification->id,
            'event_type' => 'status_change',
            'title' => 'Application Submitted',
            'description' => 'Your business verification application was received successfully and is under internal review.',
            'actor_id' => $user->id,
            'metadata' => ['status' => 'under_review', 'channel' => 'mobile_api'],
        ]);

        // Add audit trail
        VerificationAuditLog::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'submit_verification_application',
            'metadata' => ['channel' => 'mobile_api'],
        ]);

        $this->workflowService->recalculateStatus($verification);

        $verification->load([
            'documents.documentType',
            'documents.latestVersion',
            'timeline.actor'
        ]);

        return $this->successResponse(
            new CompanyVerificationResource($verification),
            'Your verification application has been submitted successfully and is now under review.'
        );
    }
}
