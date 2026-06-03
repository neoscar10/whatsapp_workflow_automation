<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyVerification extends Model
{
    use HasUuids;

    protected $table = 'company_verifications';

    protected $fillable = [
        'company_id',
        'status',
        'progress_percentage',
        'last_activity_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyVerificationDocument::class, 'company_verification_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(CompanyVerificationTimeline::class, 'company_verification_id')->orderBy('created_at', 'desc');
    }
}
