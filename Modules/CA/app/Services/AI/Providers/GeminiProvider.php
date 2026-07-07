<?php

namespace Modules\CA\Services\AI\Providers;

use Modules\CA\Contracts\AIProviderInterface;
use Modules\CA\Exceptions\AIProviderException;
use Modules\CA\Exceptions\AIParsingException;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiProvider implements AIProviderInterface
{
    protected ?string $apiKey = null;
    protected string $model;
    protected int $lastTokenUsage = 0;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.gemini.api_key');
        $this->model = config('ai.providers.gemini.model', 'gemini-1.5-pro');

        if (!$this->apiKey) {
            throw new AIProviderException("Gemini API key is missing. Please set GEMINI_API_KEY in your .env");
        }
    }

    public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $schema = [], ?string $filePath = null): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $parts = [
            ['text' => $systemPrompt . "\n\n" . $userPrompt]
        ];

        if ($filePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
            $mimeType = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($filePath) ?: 'application/octet-stream';
            $fileContents = \Illuminate\Support\Facades\Storage::disk('public')->get($filePath);
            $base64 = base64_encode($fileContents);

            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data' => $base64
                ]
            ];
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
            ]
        ];

        try {
            $response = Http::timeout(60)->post($url, $payload);
        } catch (Exception $e) {
            throw new AIProviderException("HTTP Error connecting to Gemini: " . $e->getMessage());
        }

        if (!$response->successful()) {
            $errorBody = json_decode($response->body(), true);
            $errorMessage = $errorBody['error']['message'] ?? $response->body();
            throw new AIProviderException("Gemini API Error: " . $errorMessage);
        }

        $data = $response->json();
        
        $this->lastTokenUsage = $data['usageMetadata']['totalTokenCount'] ?? 0;

        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($content)) {
            throw new AIParsingException("Gemini returned an empty response.");
        }

        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AIParsingException("Gemini returned invalid JSON: " . json_last_error_msg());
        }

        return $json;
    }

    public function getName(): string
    {
        return 'gemini';
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
