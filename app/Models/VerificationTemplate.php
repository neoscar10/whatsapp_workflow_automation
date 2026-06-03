<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationTemplate extends Model
{
    use HasUuids;

    protected $table = 'verification_templates';

    protected $fillable = [
        'name',
        'country_code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class, 'verification_template_id')->orderBy('sort_order');
    }
}
