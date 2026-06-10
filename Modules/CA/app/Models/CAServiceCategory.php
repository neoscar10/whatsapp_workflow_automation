<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CAServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'ca_service_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    public function compliances(): HasMany
    {
        return $this->hasMany(CACompliance::class, 'ca_service_category_id');
    }
}
