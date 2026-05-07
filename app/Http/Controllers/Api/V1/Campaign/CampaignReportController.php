<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\CampaignReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignReportController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignService $campaignService,
        protected CampaignReportService $reportService
    ) {}

    /**
     * Get campaign report summary.
     */
    public function summary(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $summary = $this->reportService->getSummary($request->user(), $campaign);

            // Add some derived metrics
            $total = $summary['total'] ?: 1;
            $summary['completion_percentage'] = round(($summary['sent'] + $summary['failed'] + $summary['skipped']) / $total * 100, 2);
            $summary['delivery_rate'] = round($summary['delivered'] / ($summary['sent'] ?: 1) * 100, 2);
            $summary['read_rate'] = round($summary['read'] / ($summary['delivered'] ?: 1) * 100, 2);
            $summary['failure_rate'] = round($summary['failed'] / ($summary['sent'] ?: 1) * 100, 2);

            return $this->successResponse($summary, 'Report summary retrieved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get failure breakdown.
     */
    public function failures(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            $breakdown = $this->reportService->getFailureBreakdown($request->user(), $campaign);

            return $this->successResponse($breakdown, 'Failure breakdown retrieved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Export campaign recipients to CSV.
     */
    public function export(Request $request, int $id)
    {
        try {
            $campaign = $this->campaignService->findForCompany($request->user(), $id);
            return $this->reportService->exportRecipientsCsv($request->user(), $campaign, $request->all());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
