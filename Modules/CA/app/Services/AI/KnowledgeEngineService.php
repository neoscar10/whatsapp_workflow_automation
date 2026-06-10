<?php

namespace Modules\CA\Services\AI;

use Modules\CA\Services\AI\Managers\AIManager;
use Modules\CA\Services\AI\Builders\BusinessTypePromptBuilder;
use Illuminate\Support\Facades\Log;
use Exception;

class KnowledgeEngineService
{
    protected AIManager $aiManager;
    protected CacheManager $cacheManager;
    protected BusinessTypePromptBuilder $promptBuilder;

    public function __construct(AIManager $aiManager, CacheManager $cacheManager, BusinessTypePromptBuilder $promptBuilder)
    {
        $this->aiManager = $aiManager;
        $this->cacheManager = $cacheManager;
        $this->promptBuilder = $promptBuilder;
    }

    public function generateComplianceKnowledge(string $businessTypeName): ?array
    {
        $systemPrompt = 'You are an expert Indian Chartered Accountant and Corporate Lawyer.';
        $userPrompt = $this->promptBuilder->buildComplianceKnowledgePrompt($businessTypeName);
        
        try {
            $provider = $this->aiManager->provider();
            $providerName = $provider->getName();

            // 1. Check cache first
            $cached = $this->cacheManager->getCachedResponse($providerName, $userPrompt);
            if ($cached) {
                return $cached;
            }

            // 2. Call configured AI provider
            $json = $provider->generateStructuredResponse($systemPrompt, $userPrompt);

            // 3. Save raw AI response to cache
            $this->cacheManager->saveResponse(
                $providerName,
                'compliance_knowledge_' . str()->slug($businessTypeName),
                $userPrompt,
                $json,
                $provider->getModel(),
                $provider->getLastTokenUsage()
            );

            // 4. Return structured data
            return $json;

        } catch (Exception $e) {
            Log::error("KnowledgeEngineService Error: " . $e->getMessage());
            throw $e;
        }
    }
}
