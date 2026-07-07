<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CAClientAutomation extends Model
{
    use HasFactory;

    protected $table = 'ca_client_automations';

    protected $fillable = [
        'company_id',
        'client_id',
        'automation_library_id',
        'whatsapp_template_id',
        'frequency',
        'status',
        'is_enabled',
        'created_by',
        'metadata_json',
    ];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'metadata_json' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CAClient::class, 'client_id');
    }

    public function automationLibrary(): BelongsTo
    {
        return $this->belongsTo(CAAutomationLibrary::class, 'automation_library_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CAClientAutomationRule::class, 'client_automation_id')->orderBy('sequence');
    }

    public function documentMappings(): HasMany
    {
        return $this->hasMany(CAClientAutomationDocument::class, 'client_automation_id');
    }

    public function requirements()
    {
        return $this->hasManyThrough(
            CAClientComplianceRequirement::class,
            CAClientAutomationDocument::class,
            'client_automation_id',
            'id',
            'id',
            'ca_client_compliance_requirement_id'
        );
    }

    public function whatsappTemplate(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WhatsApp\WhatsAppTemplate::class, 'whatsapp_template_id');
    }
}
