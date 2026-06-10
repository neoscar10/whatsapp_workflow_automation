<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CACompliance;
use Illuminate\Database\Eloquent\Collection;

class ComplianceService
{
    public function getAllActive(): Collection
    {
        return CACompliance::with('serviceCategory', 'deadlines')->where('status', 'active')->get();
    }

    public function getCompliancesForBusinessType(int $businessTypeId): Collection
    {
        $businessType = (new BusinessTypeService())->getWithCompliances($businessTypeId);
        return $businessType ? $businessType->compliances : collect();
    }
}
