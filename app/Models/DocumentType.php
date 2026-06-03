<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentType extends Model
{
    use HasUuids;

    protected $table = 'document_types';

    protected $fillable = [
        'verification_template_id',
        'name',
        'description',
        'placeholder',
        'accepted_formats',
        'max_size_mb',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'max_size_mb' => 'integer',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function verificationTemplate(): BelongsTo
    {
        return $this->belongsTo(VerificationTemplate::class, 'verification_template_id');
    }
}
