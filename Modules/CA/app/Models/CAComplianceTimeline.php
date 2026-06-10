<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\User;

class CAComplianceTimeline extends Model
{
    protected $table = 'ca_compliance_timelines';

    protected $fillable = [
        'company_id',
        'ca_client_id',
        'ca_client_compliance_id',
        'ca_client_compliance_requirement_id',
        'ca_document_id',
        'event_key',
        'title',
        'description',
        'actor_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(CAClient::class, 'ca_client_id');
    }

    public function clientCompliance(): BelongsTo
    {
        return $this->belongsTo(CAClientCompliance::class, 'ca_client_compliance_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(CAClientComplianceRequirement::class, 'ca_client_compliance_requirement_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CADocument::class, 'ca_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
