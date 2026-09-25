<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionJobController extends Controller
{
    use RespondsWithApiResponse;

    /**
     * Get configuration options and metadata for production jobs.
     */
    public function options(Request $request): JsonResponse
    {
        $options = [
            'statuses' => [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
            ],
            'queues' => [
                'default',
                'high',
                'low',
                'campaigns',
                'webhooks',
                'notifications',
            ],
            'priorities' => [
                'low',
                'medium',
                'high',
                'urgent',
            ],
            'job_types' => [
                'campaign_dispatch',
                'push_notification',
                'webhook_dispatch',
                'automation_process',
                'contact_import',
            ],
        ];

        return $this->successResponse($options, 'Production job options retrieved successfully.');
    }

    /**
     * Display the specified production job details.
     */
    public function show(Request $request, string|int $id): JsonResponse
    {
        $job = DB::table('jobs')->where('id', $id)->first();

        if (!$job) {
            $job = DB::table('failed_jobs')->where('id', $id)->first();
        }

        if (!$job) {
            $job = DB::table('job_batches')->where('id', $id)->first();
        }

        if (!$job) {
            return $this->errorResponse('Production job not found.', [], 404);
        }

        return $this->successResponse($job, 'Production job retrieved successfully.');
    }
}
