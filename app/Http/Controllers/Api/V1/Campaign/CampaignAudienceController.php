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
}
