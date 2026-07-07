<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CAAutomationLibrary extends Model
{
    use HasFactory;

    protected $table = 'ca_automation_library';

    protected $fillable = [
        'name',
        'slug',
        'frequency',
        'description',
        'ai_prompt_version',
        'default_template_reference',
        'icon',
        'color',
        'status',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];

    public function clientAutomations(): HasMany
    {
        return $this->hasMany(CAClientAutomation::class, 'automation_library_id');
    }

    public function aiTemplates(): HasMany
    {
        return $this->hasMany(CAAIAutomationTemplate::class, 'automation_library_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
