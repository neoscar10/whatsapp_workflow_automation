<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAClientComplianceRequirement extends Model
{
    use HasFactory;

    protected $table = 'ca_client_compliance_requirements';

    protected $fillable = [
        'ca_client_compliance_id',
        'ca_compliance_requirement_id',
        'name',
        'requirement_type',
        'input_type',
        'is_required',
        'is_recurring',
        'required_stage',
        'recurrence_frequency',
        'recurrence_config',
        'status',
        'is_completed',
        'due_date',
        'next_due_date',
        'submitted_at',
        'approved_at',
        'remarks',
        'metadata_json',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_recurring' => 'boolean',
        'is_completed' => 'boolean',
        'due_date' => 'date',
        'next_due_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'recurrence_config' => 'array',
        'metadata_json' => 'array',
    ];

    public function clientCompliance(): BelongsTo
    {
        return $this->belongsTo(CAClientCompliance::class, 'ca_client_compliance_id');
    }

    public function complianceRequirement(): BelongsTo
    {
        return $this->belongsTo(CAComplianceRequirement::class, 'ca_compliance_requirement_id');
    }

    public function automationDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CAClientAutomationDocument::class, 'ca_client_compliance_requirement_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CADocument::class, 'ca_client_compliance_requirement_id');
    }
}
