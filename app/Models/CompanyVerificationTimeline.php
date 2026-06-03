<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyVerificationTimeline extends Model
{
    use HasUuids;

    protected $table = 'company_verification_timelines';

    protected $fillable = [
        'company_verification_id',
        'event_type',
        'title',
        'description',
        'actor_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(CompanyVerification::class, 'company_verification_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
