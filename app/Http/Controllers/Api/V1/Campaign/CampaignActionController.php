<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Campaign\ScheduleCampaignRequest;
use App\Http\Resources\Api\V1\Campaign\CampaignDetailResource;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignActionController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignService $campaignService,
        protected CampaignDispatchService $dispatchService
    ) {}

    /**
     * Queue campaign for immediate sending.
     */
    public function send(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            
            // Basic validation before queuing
            if ($campaign->recipient_count === 0) {
                return $this->errorResponse('Campaign must have at least one recipient before sending.');
            }

            if ($campaign->type === 'template' && !$campaign->whatsapp_template_id) {
                return $this->errorResponse('Campaign template must be selected.');
            }

            $this->dispatchService->dispatchCampaign($campaign);

            return $this->successResponse(null, 'Campaign queued for sending successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Schedule a campaign.
     */
    public function schedule(ScheduleCampaignRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->schedule($request->user(), $campaign, $request->scheduled_at);

            return $this->successResponse(
                new CampaignDetailResource($campaign->refresh()),
                'Campaign scheduled successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Pause a sending campaign.
     */
    public function pause(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->pause($request->user(), $campaign);

            return $this->successResponse(null, 'Campaign paused successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Resume a paused campaign.
     */
    public function resume(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->resume($request->user(), $campaign);

            return $this->successResponse(null, 'Campaign resumed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $this->campaignService->cancel($request->user(), $campaign);

            return $this->successResponse(null, 'Campaign cancelled successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Duplicate a campaign.
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $newCampaign = $this->campaignService->duplicate($request->user(), $campaign);

            return $this->successResponse(
                new CampaignDetailResource($newCampaign),
                'Campaign duplicated successfully.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Retry all failed recipients.
     */
    public function retryFailed(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            
            if ($campaign->failed_count === 0) {
                return $this->errorResponse('No failed recipients available for retry.');
            }

            $this->dispatchService->retryFailed($campaign);

            return $this->successResponse(
                ['retried_count' => $campaign->failed_count], 
                'Retry jobs dispatched successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
