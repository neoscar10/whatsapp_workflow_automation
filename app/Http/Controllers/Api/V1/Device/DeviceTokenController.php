<?php

namespace App\Http\Controllers\Api\V1\Device;

use App\Http\Controllers\Controller;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register or update a mobile FCM device token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => 'required|string|max:512',
            'device_type' => 'required|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        if (!$user->company_id) {
            return response()->json(['message' => 'User does not belong to a company.'], 403);
        }

        $deviceToken = UserDeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_token' => $validated['device_token'],
            ],
            [
                'company_id' => $user->company_id,
                'device_type' => $validated['device_type'],
                'device_name' => $validated['device_name'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device token registered successfully.',
            'data' => [
                'id' => $deviceToken->id,
                'device_type' => $deviceToken->device_type,
                'device_name' => $deviceToken->device_name,
                'registered_at' => $deviceToken->created_at->toIso8601String(),
            ]
        ], 200);
    }

    /**
     * Unregister a mobile device token (on logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => 'required|string|max:512',
        ]);

        UserDeviceToken::where('user_id', $request->user()->id)
            ->where('device_token', $validated['device_token'])
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Device token unregistered successfully.'
        ], 200);
    }
}
