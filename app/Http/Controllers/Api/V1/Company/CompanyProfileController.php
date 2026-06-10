<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Company\UpdateCompanyProfileRequest;
use App\Services\Company\CompanyProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    use RespondsWithApiResponse;

    protected CompanyProfileService $profileService;

    public function __construct(CompanyProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get the current company profile data.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->company_id) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $data = $this->profileService->getProfileDataForUser($user);
        
        if (!$data) {
            return $this->errorResponse('Company profile not found.', [], 404);
        }

        $company = $user->company;
        $data['is_verified'] = $company ? $company->isVerified() : false;

        return $this->successResponse($data, 'Company profile retrieved successfully.');
    }

    /**
     * Update the company profile data.
     */
    public function update(UpdateCompanyProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->company_id) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        $validatedData = $request->validated();
        
        $profileData = [
            'company_name' => $validatedData['company_name'],
            'contact_email' => $validatedData['contact_email'],
            'website_url' => $validatedData['website_url'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'country' => $validatedData['country'],
        ];

        try {
            $updatedData = $this->profileService->updateProfileForUser(
                $user, 
                $profileData, 
                $request->file('logo')
            );

            $company = $user->company;
            $updatedData['is_verified'] = $company ? $company->isVerified() : false;

            return $this->successResponse($updatedData, 'Company profile updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update company profile: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the company logo.
     */
    public function removeLogo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->company_id) {
            return $this->errorResponse('User does not belong to a company.', [], 400);
        }

        try {
            $updatedData = $this->profileService->removeLogoForUser($user);

            $company = $user->company;
            $updatedData['is_verified'] = $company ? $company->isVerified() : false;

            return $this->successResponse($updatedData, 'Company logo removed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove company logo: ' . $e->getMessage(), [], 500);
        }
    }
}
