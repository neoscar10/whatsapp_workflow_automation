<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CAAICache extends Model
{
    use HasFactory;

    protected $table = 'ca_ai_cache';

    protected $fillable = [
        'provider_name',
        'cache_key',
        'request_hash',
        'prompt',
        'response_json',
        'model_used',
        'token_usage',
        'cache_version',
        'expires_at',
    ];

    protected $casts = [
        'response_json' => 'array',
        'expires_at' => 'datetime',
    ];
}
