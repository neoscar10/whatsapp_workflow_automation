<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPackage extends Model
{
    use HasUuids;

    protected $table = 'company_packages';

    protected $fillable = [
        'company_id',
        'payment_transaction_id',
        'amount',
        'remaining_balance',
        'text_rate',
        'template_utility_rate',
        'template_auth_rate',
        'template_marketing_rate',
        'automation_rate',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'text_rate' => 'decimal:4',
        'template_utility_rate' => 'decimal:4',
        'template_auth_rate' => 'decimal:4',
        'template_marketing_rate' => 'decimal:4',
        'automation_rate' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
