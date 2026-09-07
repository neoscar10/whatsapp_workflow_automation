<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyModeController extends Controller
{
    /**
     * Toggle or set the active mode (live vs demo) for the authenticated user's company.
     */
    public function toggleMode(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company associated with user account.',
            ], 404);
        }

        // Check if company has live access enabled by super admin
        if (!$company->can_access_live) {
            return response()->json([
                'success' => false,
                'message' => 'Live mode access is disabled for your company by system administrator.',
                'data' => [
                    'can_access_live' => false,
                    'status' => 'demo',
                    'is_demo' => true,
                ],
            ], 403);
        }

        $request->validate([
            'status' => 'nullable|string|in:active,live,demo,toggle',
            'mode' => 'nullable|string|in:active,live,demo,toggle',
        ]);

        $requestedMode = $request->input('status') ?? $request->input('mode', 'toggle');

        if ($requestedMode === 'live' || $requestedMode === 'active') {
            $newStatus = 'active';
        } elseif ($requestedMode === 'demo') {
            $newStatus = 'demo';
        } else {
            // Toggle current status
            $newStatus = $company->status === 'demo' ? 'active' : 'demo';
        }

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'active') {
            $updateData['demo_ends_at'] = null;
        }

        $company->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Company mode updated successfully.',
            'data' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'status' => $company->status,
                'is_demo' => ($company->status === 'demo'),
                'can_access_live' => (bool) $company->can_access_live,
                'demo_credits' => (float) ($company->demo_credits ?? 0.0),
                'demo_ends_at' => $company->demo_ends_at?->toIso8601String(),
            ],
        ]);
    }
}
