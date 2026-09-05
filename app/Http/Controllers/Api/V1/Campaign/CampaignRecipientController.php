<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Campaign\ListCampaignRecipientsRequest;
use App\Http\Resources\Api\V1\Campaign\CampaignRecipientResource;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignReportService;
use App\Services\Campaign\CampaignDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignRecipientController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignService $campaignService,
        protected CampaignReportService $reportService,
        protected CampaignDispatchService $dispatchService
    ) {}

    /**
     * List campaign recipients.
     */
    public function index(ListCampaignRecipientsRequest $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $recipients = $this->reportService->listRecipients(
                $request->user(), 
                $campaign, 
                $request->all()
            );

            $resourceData = CampaignRecipientResource::collection($recipients)->response()->getData(true);
            $items = $resourceData['data'] ?? [];
            $meta = $resourceData['meta'] ?? [];
            $links = $resourceData['links'] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Recipients retrieved successfully.',
                'data' => $items,
                'recipients' => $items,
                'meta' => $meta,
                'links' => $links,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Retry a single failed recipient.
     */
    public function retry(Request $request, int $campaignId, int $recipientId): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $campaignId);
            $recipient = $campaign->recipients()->findOrFail($recipientId);

            $this->dispatchService->retryRecipient($recipient);

            return $this->successResponse(
                new CampaignRecipientResource($recipient->refresh()),
                'Recipient retry dispatched successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
