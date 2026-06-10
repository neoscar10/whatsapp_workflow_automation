<?php

namespace Modules\CA\Services\AI\Providers;

use Modules\CA\Contracts\AIProviderInterface;
use Modules\CA\Exceptions\AIProviderException;
use Modules\CA\Exceptions\AIParsingException;
use OpenAI;
use Exception;

class OpenAIProvider implements AIProviderInterface
{
    protected $client;
    protected string $model;
    protected int $lastTokenUsage = 0;

    public function __construct()
    {
        $apiKey = config('ai.providers.openai.api_key');
        $this->model = config('ai.providers.openai.model', 'gpt-4o');

        if (!$apiKey) {
            throw new AIProviderException("OpenAI API key is missing. Please set OPENAI_API_KEY in your .env");
        }

        $this->client = OpenAI::client($apiKey);
    }

    public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $schema = []): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
            ]);
        } catch (Exception $e) {
            throw new AIProviderException("Error connecting to OpenAI: " . $e->getMessage());
        }

        $content = $response->choices[0]->message->content ?? '';
        $this->lastTokenUsage = $response->usage->totalTokens ?? 0;

        if (empty($content)) {
            throw new AIParsingException("OpenAI returned an empty response.");
        }

        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AIParsingException("OpenAI returned invalid JSON: " . json_last_error_msg());
        }

        return $json;
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getLastTokenUsage(): int
    {
        return $this->lastTokenUsage;
    }
}
