<?php

namespace App\Services\Company;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class CompanyProfileService
{
    /**
     * Get company profile data for the authenticated user.
     */
    public function getProfileDataForUser(User $user): array
    {
        $company = $user->company;

        if (!$company) {
            return [];
        }

        return [
            'company_name' => $company->name,
            'contact_email' => $company->primary_email,
            'website_url' => $company->website_url,
            'description' => $company->description,
            'logo_url' => $company->logo_path ? Storage::disk('public')->url($company->logo_path) : null,
        ];
    }

    /**
     * Update company profile for the user.
     */
    public function updateProfileForUser(User $user, array $data, $logoFile = null): array
    {
        $company = $user->company;

        if (!$company) {
            throw new \Exception('User does not belong to a company.');
        }

        $company->update([
            'name' => $data['company_name'],
            'primary_email' => $data['contact_email'],
            'website_url' => $data['website_url'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        if ($logoFile instanceof UploadedFile) {
            $this->handleLogoUpload($company, $logoFile);
        }

        return $this->getProfileDataForUser($user);
    }

    /**
     * Remove the company logo.
     */
    public function removeLogoForUser(User $user): array
    {
        $company = $user->company;

        if (!$company) {
            throw new \Exception('User does not belong to a company.');
        }

        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return $this->getProfileDataForUser($user);
    }

    /**
     * Handle the logo upload logic.
     */
    protected function handleLogoUpload($company, UploadedFile $file): void
    {
        // Delete old logo if it exists
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        // Store new logo
        $path = $file->store('company-logos', 'public');
        $company->update(['logo_path' => $path]);
    }
}
