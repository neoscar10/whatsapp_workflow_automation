<?php

namespace App\Models\Webhooks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyWebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_webhook_id',
        'event_type',
        'payload',
        'status_code',
        'response_body',
        'error_message',
        'attempt',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(CompanyWebhook::class, 'company_webhook_id');
    }
}
