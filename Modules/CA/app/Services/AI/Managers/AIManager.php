<?php

namespace Modules\CA\Services\AI\Managers;

use Modules\CA\Contracts\AIProviderInterface;
use Modules\CA\Services\AI\Providers\GeminiProvider;
use Modules\CA\Services\AI\Providers\OpenAIProvider;
use Exception;

class AIManager
{
    /**
     * Get the configured AI provider instance.
     *
     * @return AIProviderInterface
     * @throws Exception
     */
    public function provider(?string $name = null): AIProviderInterface
    {
        $name = $name ?: config('ai.default', 'gemini');

        switch (strtolower($name)) {
            case 'gemini':
                return new GeminiProvider();
            case 'openai':
                return new OpenAIProvider();
            default:
                throw new Exception("Unsupported AI Provider configured: {$name}");
        }
    }
}
