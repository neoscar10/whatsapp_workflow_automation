<?php

namespace Modules\CA\Services\AI;

use Modules\CA\Models\CAAICache;
use Illuminate\Support\Facades\Log;

class CacheManager
{
    public const CACHE_VERSION = 3; // Incremented for Phase 3 V2 requirements schema with document_type and required_stage

    public function getCachedResponse(string $providerName, string $prompt, int $version = self::CACHE_VERSION)
    {
        $hash = md5($prompt);
        $cache = CAAICache::where('provider_name', $providerName)
            ->where('request_hash', $hash)
            ->where('cache_version', $version)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();
            
        if ($cache) {
            Log::info("CA AI Cache hit for provider {$providerName}, hash: {$hash} (Version: {$version})");
            return $cache->response_json;
        }
        
        return null;
    }
    
    public function saveResponse(string $providerName, string $cacheKey, string $prompt, array $responseJson, string $modelUsed, int $tokenUsage = null, $expiresAt = null, int $version = self::CACHE_VERSION)
    {
        return CAAICache::create([
            'provider_name' => $providerName,
            'cache_key' => $cacheKey . '_' . time(),
            'request_hash' => md5($prompt),
            'prompt' => $prompt,
            'response_json' => $responseJson,
            'model_used' => $modelUsed,
            'token_usage' => $tokenUsage,
            'cache_version' => $version,
            'expires_at' => $expiresAt ?? now()->addDays(30),
        ]);
    }
}
