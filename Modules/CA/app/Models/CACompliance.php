<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CACompliance extends Model
{
    use HasFactory;

    protected $table = 'ca_compliances';

    protected $fillable = [
        'ca_service_category_id',
        'name',
        'slug',
        'description',
        'is_recurring',
        'status',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(CAServiceCategory::class, 'ca_service_category_id');
    }

    public function businessTypes(): BelongsToMany
    {
        return $this->belongsToMany(CABusinessType::class, 'ca_business_type_compliance', 'ca_compliance_id', 'ca_business_type_id');
    }

    public function complianceDeadlines(): HasMany
    {
        return $this->hasMany(CAComplianceDeadline::class, 'ca_compliance_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(CAComplianceRequirement::class, 'ca_compliance_id');
    }
}
