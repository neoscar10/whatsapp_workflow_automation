<?php

namespace App\Models\WhatsApp;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppAccount extends Model
{
    protected $table = 'whatsapp_accounts';

    protected $fillable = [
        'company_id',
        'access_token',
        'waba_id',
        'business_id',
        'connection_status',
        'webhook_status',
        'webhook_callback_url',
        'webhook_verify_token',
        'webhook_verified_at',
        'webhook_last_checked_at',
        'webhook_last_error',
        'webhook_subscription_status',
        'webhook_subscribed_at',
        'connected_at',
        'last_verified_at',
        'last_synced_at',
        'last_sync_error',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'connected_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'webhook_verified_at' => 'datetime',
        'webhook_last_checked_at' => 'datetime',
        'webhook_subscribed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(WhatsAppPhoneNumber::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(WhatsAppTemplate::class, 'whatsapp_account_id');
    }
}
