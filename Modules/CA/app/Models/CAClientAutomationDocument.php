<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAClientAutomationDocument extends Model
{
    use HasFactory;

    protected $table = 'ca_client_automation_documents';

    protected $fillable = [
        'client_automation_id',
        'ca_client_compliance_requirement_id',
    ];

    public function clientAutomation(): BelongsTo
    {
        return $this->belongsTo(CAClientAutomation::class, 'client_automation_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(CAClientComplianceRequirement::class, 'ca_client_compliance_requirement_id');
    }
}
