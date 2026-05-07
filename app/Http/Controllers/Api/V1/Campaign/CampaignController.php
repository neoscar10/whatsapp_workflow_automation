<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Campaign\ListCampaignsRequest;
use App\Http\Requests\Api\V1\Campaign\StoreCampaignRequest;
use App\Http\Requests\Api\V1\Campaign\UpdateCampaignRequest;
use App\Http\Requests\Api\V1\Campaign\UpdateCampaignContentRequest;
use App\Http\Resources\Api\V1\Campaign\CampaignResource;
use App\Http\Resources\Api\V1\Campaign\CampaignDetailResource;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignService $campaignService
    ) {}

    /**
     * Display a listing of campaigns.
     */
    public function index(ListCampaignsRequest $request): JsonResponse
    {
        $campaigns = $this->campaignService->listForCompany(
            $request->user(), 
            $request->validated()
        );

        return $this->successResponse(
            CampaignResource::collection($campaigns)->response()->getData(true),
            'Campaigns retrieved successfully.'
        );
    }

    /**
     * Store a newly created campaign.
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        try {
            $campaign = $this->campaignService->createDraft(
                $request->user(), 
                $request->validated()
            );

            return $this->successResponse(
                new CampaignDetailResource($campaign),
                'Campaign created successfully.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified campaign.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            return $this->successResponse(new CampaignDetailResource($campaign));
        } catch (\Exception $e) {
            return $this->errorResponse('Campaign not found for your company.', [], 404);
        }
    }

    /**
     * Update the specified campaign details.
     */
    public function update(UpdateCampaignRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->update($request->user(), $campaign, $request->validated());

            return $this->successResponse(
                new CampaignDetailResource($campaign->refresh()),
                'Campaign updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update campaign message content.
     */
    public function updateContent(UpdateCampaignContentRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->updateContent($request->user(), $campaign, $request->validated());

            return $this->successResponse(
                new CampaignDetailResource($campaign->refresh()),
                'Campaign content updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->deleteForCompany($request->user(), $campaign);

            return $this->successResponse(null, 'Campaign deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
