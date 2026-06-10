<?php

namespace Modules\CA\Tests\Feature;

use Tests\TestCase;
use Modules\CA\Services\AI\Managers\AIManager;
use Modules\CA\Services\AI\Providers\GeminiProvider;
use Modules\CA\Services\AI\Providers\OpenAIProvider;
use Modules\CA\Exceptions\AIProviderException;
use Illuminate\Support\Facades\Config;

class AIManagerTest extends TestCase
{
    public function test_it_instantiates_gemini_provider_by_default()
    {
        Config::set('ai.default', 'gemini');
        Config::set('ai.providers.gemini.api_key', 'test_key');
        
        $manager = new AIManager();
        $provider = $manager->provider();
        
        $this->assertInstanceOf(GeminiProvider::class, $provider);
        $this->assertEquals('gemini', $provider->getName());
    }

    public function test_it_instantiates_openai_provider_when_configured()
    {
        Config::set('ai.default', 'openai');
        Config::set('ai.providers.openai.api_key', 'test_key');
        
        $manager = new AIManager();
        $provider = $manager->provider();
        
        $this->assertInstanceOf(OpenAIProvider::class, $provider);
        $this->assertEquals('openai', $provider->getName());
    }

    public function test_it_throws_exception_on_missing_api_key()
    {
        Config::set('ai.default', 'gemini');
        Config::set('ai.providers.gemini.api_key', null);
        
        $this->expectException(AIProviderException::class);
        $this->expectExceptionMessage('Gemini API key is missing');
        
        $manager = new AIManager();
        $provider = $manager->provider();
    }
}
