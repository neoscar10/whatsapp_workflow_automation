<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Company\UploadVerificationDocumentRequest;
use App\Http\Resources\Api\V1\Company\CompanyVerificationResource;
use App\Http\Resources\Api\V1\Company\DocumentTypeResource;
use App\Http\Resources\Api\V1\Company\VerificationDocumentVersionResource;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\DocumentType;
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
     * Get the current verification status and required documents.
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

        $documentTypes = DocumentType::where('is_active', true)->orderBy('sort_order')->get();

        $data = [
            'verification' => new CompanyVerificationResource($verification),
            'required_document_types' => DocumentTypeResource::collection($documentTypes),
        ];

        return $this->successResponse($data, 'Verification status retrieved successfully.');
    }

    /**
     * Upload a new verification document.
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
    public function history(Request $request, int $documentTypeId): JsonResponse
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
}
