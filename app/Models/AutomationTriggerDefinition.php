<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationTriggerDefinition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'key',
        'name',
        'category',
        'subtype',
        'description',
        'is_system',
        'is_read_only',
        'config_schema',
        'default_config',
        'default_output_variables',
        'status',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'config_schema' => 'array',
        'default_config' => 'array',
        'default_output_variables' => 'array',
        'is_system' => 'boolean',
        'is_read_only' => 'boolean',
        'status' => 'boolean',
    ];

    public function scopeForCompany($query, $companyId = null)
    {
        return $query->where(function ($q) use ($companyId) {
            $q->whereNull('company_id')
              ->orWhere('company_id', $companyId);
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
