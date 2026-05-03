<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Api\Auth\ApiAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ApiAuthService $authService
    ) {}

    /**
     * Handle a login request.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $deviceName = $request->input('device_name', 'mobile_app');
            $result = $this->authService->login($request->only('email', 'password'), $deviceName);

            return $this->successResponse([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'user' => new UserResource($result['user']),
            ], 'Login successful.');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred during login.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()), 'User profile retrieved.');
    }

    /**
     * Handle a logout request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Revoke all tokens for the user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->successResponse(null, 'All sessions logged out successfully.');
    }
}
