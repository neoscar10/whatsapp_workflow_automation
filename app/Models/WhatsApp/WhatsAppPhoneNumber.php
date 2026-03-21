<?php

namespace App\Models\WhatsApp;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppPhoneNumber extends Model
{
    protected $table = 'whatsapp_phone_numbers';

    protected $fillable = [
        'company_id',
        'whatsapp_account_id',
        'display_name',
        'phone_number_id',
        'phone_number',
        'status',
        'created_by_user_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
