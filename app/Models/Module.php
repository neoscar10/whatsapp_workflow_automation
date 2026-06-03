<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasUuids;

    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'is_core',
        'is_active',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function companyAssignments(): HasMany
    {
        return $this->hasMany(CompanyModule::class, 'module_id');
    }
}
