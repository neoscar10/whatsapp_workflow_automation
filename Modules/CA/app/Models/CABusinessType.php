<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CABusinessType extends Model
{
    use HasFactory;

    protected $table = 'ca_business_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];

    public function compliances(): BelongsToMany
    {
        return $this->belongsToMany(CACompliance::class, 'ca_business_type_compliance', 'ca_business_type_id', 'ca_compliance_id');
    }
}
