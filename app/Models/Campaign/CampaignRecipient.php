<?php

namespace App\Models\Campaign;

use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'company_id',
        'contact_id',
        'conversation_id',
        'conversation_message_id',
        'phone',
        'normalized_phone',
        'name',
        'source',
        'status',
        'skip_reason',
        'personalization_data',
        'resolved_template_payload',
        'provider_message_id',
        'meta_error_code',
        'meta_error_message',
        'meta_error_payload',
        'attempts',
        'last_attempted_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'personalization_data' => 'array',
        'resolved_template_payload' => 'array',
        'meta_error_payload' => 'array',
        'last_attempted_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function conversationMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class);
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

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Helpers
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed';
    }

    public function markQueued()
    {
        $this->update(['status' => 'queued']);
    }

    public function markSending()
    {
        $this->update(['status' => 'sending', 'last_attempted_at' => now()]);
    }

    public function markSent(?string $providerMessageId = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => $providerMessageId,
            'meta_error_code' => null,
            'meta_error_message' => null,
        ]);
    }

    public function markFailed(string $code, ?string $message = null, ?array $payload = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'meta_error_code' => $code,
            'meta_error_message' => $message,
            'meta_error_payload' => $payload,
        ]);
    }

    public function markSkipped(string $reason)
    {
        $this->update(['status' => 'skipped', 'skip_reason' => $reason]);
    }
}
