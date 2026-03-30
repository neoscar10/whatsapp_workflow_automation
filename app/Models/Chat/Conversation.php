<?php

namespace App\Models\Chat;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_customer_message_at' => 'datetime',
        'assigned_at' => 'datetime',
        'labels' => 'array',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function whatsappPhoneNumber(): BelongsTo
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class);
    }

    /**
     * Determine if the WhatsApp 24-hour service window is currently active.
     */
    public function getIsSessionActiveAttribute(): bool
    {
        if (!$this->last_customer_message_at) {
            return false;
        }

        return $this->last_customer_message_at->diffInHours(now()) < 24;
    }
}
