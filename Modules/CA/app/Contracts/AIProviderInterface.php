<?php

namespace Modules\CA\Contracts;

interface AIProviderInterface
{
    /**
     * Generate a structured JSON response from the AI provider.
     *
     * @param string $systemPrompt The system instruction / rules.
     * @param string $userPrompt The user's prompt.
     * @param array $schema Optional JSON schema mapping if supported.
     * @return array The decoded JSON array.
     * @throws \Modules\CA\Exceptions\AIProviderException
     * @throws \Modules\CA\Exceptions\AIParsingException
     */
    public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $schema = []): array;

    /**
     * Get the name of the provider.
     */
    public function getName(): string;

    /**
     * Get the model being used by this provider.
     */
    public function getModel(): string;
    
    /**
     * Get the token usage from the last request.
     */
    public function getLastTokenUsage(): int;
}
