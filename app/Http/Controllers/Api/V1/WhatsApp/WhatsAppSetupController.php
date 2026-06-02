<?php

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\WhatsApp\Setup\StoreWhatsAppPhoneNumberRequest;
use App\Http\Requests\Api\V1\WhatsApp\Setup\UpdateWhatsAppAccountRequest;
use App\Http\Requests\Api\V1\WhatsApp\Setup\UpdateWhatsAppPhoneNumberRequest;
use App\Http\Resources\Api\V1\WhatsApp\WhatsAppAccountResource;
use App\Http\Resources\Api\V1\WhatsApp\WhatsAppPhoneNumberResource;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppAccountSetupService;
use App\Services\WhatsApp\WhatsAppPhoneNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppSetupController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected WhatsAppAccountSetupService $accountSetupService,
        protected WhatsAppPhoneNumberService $phoneNumberService
    ) {}

    /**
     * Get WhatsApp account setup data.
     */
    public function account(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $account = WhatsAppAccount::where('company_id', $company->id)->first();

        if (!$account) {
            return $this->errorResponse('WhatsApp account not found.', [], 404);
        }

        return $this->successResponse(
            new WhatsAppAccountResource($account),
            'WhatsApp account details retrieved successfully.'
        );
    }

    /**
     * Update WhatsApp account setup data.
     */
    public function updateAccount(UpdateWhatsAppAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        try {
            $data = $request->validated();
            $setupData = $this->accountSetupService->saveSetupForUser($user, $data);

            return $this->successResponse(
                $setupData,
                'WhatsApp account setup updated successfully.'
            );
        } catch (\Exception $e) {
            Log::error('API WhatsApp Account Setup Update Error', ['message' => $e->getMessage(), 'company_id' => $company->id]);
            return $this->errorResponse('Failed to update WhatsApp account setup: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get WhatsApp phone numbers.
     */
    public function phoneNumbers(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $filters = $request->only(['search', 'status', 'per_page']);
        $phoneNumbers = $this->phoneNumberService->paginateForUser($user, $filters);

        return $this->successResponse(
            WhatsAppPhoneNumberResource::collection($phoneNumbers)->response()->getData(true),
            'WhatsApp phone numbers retrieved successfully.'
        );
    }

    /**
     * Store a new WhatsApp phone number.
     */
    public function storePhoneNumber(StoreWhatsAppPhoneNumberRequest $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        try {
            $data = $request->validated();
            $phoneNumber = $this->phoneNumberService->createNumberForUser($user, $data);

            return $this->successResponse(
                new WhatsAppPhoneNumberResource($phoneNumber),
                'WhatsApp phone number created successfully.',
                201
            );
        } catch (\Exception $e) {
            Log::error('API WhatsApp Phone Number Creation Error', ['message' => $e->getMessage(), 'company_id' => $company->id]);
            return $this->errorResponse($e->getMessage(), [], 400);
        }
    }

    /**
     * Update a WhatsApp phone number.
     */
    public function updatePhoneNumber(UpdateWhatsAppPhoneNumberRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        try {
            $data = $request->validated();
            $phoneNumber = $this->phoneNumberService->updateNumberForUser($user, $id, $data);

            return $this->successResponse(
                new WhatsAppPhoneNumberResource($phoneNumber),
                'WhatsApp phone number updated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Phone number not found.', [], 404);
        } catch (\Exception $e) {
            Log::error('API WhatsApp Phone Number Update Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to update phone number: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Toggle WhatsApp phone number status.
     */
    public function togglePhoneNumberStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        if ($company->status === 'demo') {
            return $this->errorResponse('Cannot modify settings while in Demo Mode.', [], 403);
        }

        try {
            $phoneNumber = $this->phoneNumberService->toggleStatusForUser($user, $id);

            return $this->successResponse(
                new WhatsAppPhoneNumberResource($phoneNumber),
                'WhatsApp phone number status toggled successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Phone number not found.', [], 404);
        } catch (\Exception $e) {
            Log::error('API WhatsApp Phone Number Status Toggle Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to toggle phone number status: ' . $e->getMessage(), [], 500);
        }
    }
}
