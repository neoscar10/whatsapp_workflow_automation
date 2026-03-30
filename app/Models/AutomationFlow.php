<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationFlow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'status',
        'trigger_summary',
        'total_executions',
        'last_run_at',
        'is_enabled',
        'builder_version',
        'canvas_meta',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
        'is_enabled' => 'boolean',
        'total_executions' => 'integer',
        'canvas_meta' => 'array',
    ];

    /**
     * Scope a query to only include automations for the current company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function nodes()
    {
        return $this->hasMany(AutomationNode::class, 'automation_flow_id');
    }

    public function connections()
    {
        return $this->hasMany(AutomationConnection::class, 'automation_flow_id');
    }

    /**
     * Get visual meta for the current status.
     */
    public function getStatusMetaAttribute(): array
    {
        return match ($this->status) {
            'active' => [
                'label' => 'Active',
                'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'icon' => 'check_circle',
            ],
            'paused' => [
                'label' => 'Paused',
                'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                'icon' => 'pause_circle',
            ],
            default => [
                'label' => 'Draft',
                'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                'icon' => 'draft',
            ],
        };
    }
}
