<?php

namespace App\Models\Campaign;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'updated_by',
        'whatsapp_phone_number_id',
        'whatsapp_template_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'audience_type',
        'audience_filters',
        'message_body',
        'template_name',
        'template_language',
        'template_components',
        'template_variable_mapping',
        'default_variable_values',
        'personalization_config',
        'recipient_count',
        'eligible_recipient_count',
        'skipped_recipient_count',
        'sent_count',
        'delivered_count',
        'read_count',
        'failed_count',
        'pending_count',
        'scheduled_at',
        'started_at',
        'completed_at',
        'paused_at',
        'cancelled_at',
        'last_dispatched_at',
        'failure_reason',
        'meta',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'template_components' => 'array',
        'template_variable_mapping' => 'array',
        'default_variable_values' => 'array',
        'personalization_config' => 'array',
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_dispatched_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function whatsappPhoneNumber(): BelongsTo
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    public function whatsappTemplate(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeScheduledDue($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Helpers
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'paused']);
    }

    public function canSend(): bool
    {
        return in_array($this->status, ['draft', 'paused', 'failed']);
    }

    public function canPause(): bool
    {
        return in_array($this->status, ['queued', 'sending']);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    public function canDuplicate(): bool
    {
        return true;
    }
}
