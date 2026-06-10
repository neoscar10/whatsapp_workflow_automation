<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAClientComplianceDeadline extends Model
{
    use HasFactory;

    protected $table = 'ca_client_compliance_deadlines';

    protected $fillable = [
        'ca_client_compliance_id',
        'ca_client_compliance_requirement_id',
        'deadline_name',
        'deadline_type',
        'due_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function clientCompliance(): BelongsTo
    {
        return $this->belongsTo(CAClientCompliance::class, 'ca_client_compliance_id');
    }

    public function clientComplianceRequirement(): BelongsTo
    {
        return $this->belongsTo(CAClientComplianceRequirement::class, 'ca_client_compliance_requirement_id');
    }
}
