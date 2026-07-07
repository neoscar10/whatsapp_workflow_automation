<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAReminderActivity extends Model
{
    use HasFactory;

    protected $table = 'ca_reminder_activities';

    protected $fillable = [
        'company_id',
        'ca_client_automation_id',
        'ca_client_automation_rule_id',
        'ca_client_compliance_requirement_id',
        'status',
        'external_message_id',
        'error_message',
        'response_payload',
        'retry_count',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'sent_at'          => 'datetime',
        'delivered_at'     => 'datetime',
        'read_at'          => 'datetime',
        'retry_count'      => 'integer',
    ];

    public function clientAutomation(): BelongsTo
    {
        return $this->belongsTo(CAClientAutomation::class, 'ca_client_automation_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CAClientAutomationRule::class, 'ca_client_automation_rule_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(CAClientComplianceRequirement::class, 'ca_client_compliance_requirement_id');
    }
}
