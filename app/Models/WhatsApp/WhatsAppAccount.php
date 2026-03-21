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
        'connected_at',
        'last_verified_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'connected_at' => 'datetime',
        'last_verified_at' => 'datetime',
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
