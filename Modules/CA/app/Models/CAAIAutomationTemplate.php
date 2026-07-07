<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAAIAutomationTemplate extends Model
{
    use HasFactory;

    protected $table = 'ca_ai_automation_templates';

    public const CURRENT_CACHE_VERSION = '1.0';
    public const PROMPT_VERSION        = '1.0';

    protected $fillable = [
        'automation_library_id',
        'frequency',
        'language',
        'tone',
        'message_title',
        'message_body',
        'prompt_version',
        'ai_provider',
        'ai_model',
        'cache_version',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];

    public function automationLibrary(): BelongsTo
    {
        return $this->belongsTo(CAAutomationLibrary::class, 'automation_library_id');
    }

    /**
     * Scope to find a specific matching template for a library item.
     */
    public function scopeForLibrary($query, int $libraryId, string $language = 'en', string $tone = 'professional')
    {
        return $query
            ->where('automation_library_id', $libraryId)
            ->where('language', $language)
            ->where('tone', $tone)
            ->where('cache_version', self::CURRENT_CACHE_VERSION);
    }
}
