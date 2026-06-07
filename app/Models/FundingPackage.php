<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FundingPackage extends Model
{
    use HasUuids;

    protected $table = 'funding_packages';

    protected $fillable = [
        'amount',
        'text_rate',
        'template_utility_rate',
        'template_auth_rate',
        'template_marketing_rate',
        'automation_rate',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'text_rate' => 'decimal:4',
        'template_utility_rate' => 'decimal:4',
        'template_auth_rate' => 'decimal:4',
        'template_marketing_rate' => 'decimal:4',
        'automation_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
