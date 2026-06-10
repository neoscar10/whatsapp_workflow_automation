<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAComplianceRequirement extends Model
{
    use HasFactory;

    protected $table = 'ca_compliance_requirements';

    protected $fillable = [
        'ca_compliance_id',
        'name',
        'slug',
        'description',
        'requirement_type',
        'input_type',
        'is_required',
        'is_recurring',
        'required_when',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function compliance(): BelongsTo
    {
        return $this->belongsTo(CACompliance::class, 'ca_compliance_id');
    }
}
