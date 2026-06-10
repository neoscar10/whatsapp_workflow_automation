<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CAClientCompliance extends Model
{
    use HasFactory;

    protected $table = 'ca_client_compliances';

    protected $fillable = [
        'ca_client_id',
        'ca_compliance_id',
        'status',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CAClient::class, 'ca_client_id');
    }

    public function compliance(): BelongsTo
    {
        return $this->belongsTo(CACompliance::class, 'ca_compliance_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function clientRequirements()
    {
        return $this->hasMany(CAClientComplianceRequirement::class, 'ca_client_compliance_id');
    }

    public function deadlines()
    {
        return $this->hasMany(CAClientComplianceDeadline::class, 'ca_client_compliance_id');
    }

    public function documents()
    {
        return $this->hasMany(CADocument::class, 'ca_client_compliance_id');
    }
}
