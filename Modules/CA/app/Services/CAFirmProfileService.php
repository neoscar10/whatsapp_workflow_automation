<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAFirmProfile;

class CAFirmProfileService
{
    public function getProfileByCompany(int $companyId): ?CAFirmProfile
    {
        return CAFirmProfile::where('company_id', $companyId)->first();
    }

    public function createOrUpdateProfile(int $companyId, array $data): CAFirmProfile
    {
        return CAFirmProfile::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );
    }
}
