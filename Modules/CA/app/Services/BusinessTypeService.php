<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CABusinessType;
use Illuminate\Database\Eloquent\Collection;

class BusinessTypeService
{
    public function getAllActive(): Collection
    {
        return CABusinessType::where('status', 'active')->get();
    }

    public function getWithCompliances(int $businessTypeId): ?CABusinessType
    {
        return CABusinessType::with('compliances.deadlines', 'compliances.serviceCategory')->find($businessTypeId);
    }
}
