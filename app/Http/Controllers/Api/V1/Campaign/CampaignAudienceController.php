<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Campaign\PreviewCampaignAudienceRequest;
use App\Http\Requests\Api\V1\Campaign\SyncCampaignAudienceRequest;
use App\Http\Requests\Api\V1\Campaign\ImportCampaignRecipientsRequest;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignAudienceService;
use App\Services\Campaign\CampaignRecipientImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests\Api\V1\Campaign\ManualCampaignRecipientsRequest;
use App\Http\Requests\Api\V1\Campaign\UpdateCampaignRecipientRequest;

class CampaignAudienceController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignService $campaignService,
        protected CampaignAudienceService $audienceService,
        protected CampaignRecipientImportService $importService
    ) {}

    /**
     * Preview audience without saving.
     */
    public function preview(PreviewCampaignAudienceRequest $request): JsonResponse
    {
        $preview = $this->audienceService->previewAudience($request->user(), $request->validated());

        return $this->successResponse($preview, 'Audience preview generated successfully.');
    }

    /**
     * Sync audience to a campaign.
     */
    public function sync(SyncCampaignAudienceRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $summary = $this->audienceService->syncAudience($request->user(), $campaign, $request->validated());

            return $this->successResponse($summary, 'Campaign audience synced successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Import recipients from CSV.
     */
    public function import(ImportCampaignRecipientsRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            
            $file = $request->file('file');
            $summary = $this->importService->importFromCsv($request->user(), $campaign, $file->getRealPath());

            return $this->successResponse($summary, 'Recipients imported successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Add manual recipients to a campaign.
     */
    public function addManual(ManualCampaignRecipientsRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $summary = $this->audienceService->addManualRecipients($request->user(), $campaign, $request->validated()['rows']);

            return $this->successResponse($summary, 'Manual recipients added successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get detailed validation preview for a campaign's audience.
     */
    public function validationPreview(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $preview = $this->audienceService->validateAndPreviewRecipients($request->user(), $campaign);

            return $this->successResponse($preview, 'Audience validation preview retrieved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Correct and update a campaign recipient row.
     */
    public function updateRecipient(UpdateCampaignRecipientRequest $request, int $id, int $recipientId): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $result = $this->audienceService->correctRecipientRow($request->user(), $campaign, $recipientId, $request->validated());

            return $this->successResponse($result, 'Recipient updated and re-validated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
