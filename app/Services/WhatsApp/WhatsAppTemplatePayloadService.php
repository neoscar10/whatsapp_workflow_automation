<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppTemplate;

class WhatsAppTemplatePayloadService
{
    /**
     * Build the components array for a WhatsApp Cloud API template message.
     */
    public function buildSendPayload(WhatsAppTemplate $template, array $variableMappings, array $resolvedValues): array
    {
        $meta = $template->meta_payload ?? [];
        $templateComponents = $meta['components'] ?? [];
        $outboundComponents = [];

        foreach ($templateComponents as $comp) {
            $type = strtolower($comp['type']);
            $parameters = [];

            if ($type === 'header' && isset($comp['text'])) {
                $parameters = $this->extractParameters($comp['text'], $variableMappings, $resolvedValues);
                if (!empty($parameters)) {
                    $outboundComponents[] = [
                        'type' => 'header',
                        'parameters' => $parameters
                    ];
                }
            } elseif ($type === 'body' && isset($comp['text'])) {
                $parameters = $this->extractParameters($comp['text'], $variableMappings, $resolvedValues);
                if (!empty($parameters)) {
                    $outboundComponents[] = [
                        'type' => 'body',
                        'parameters' => $parameters
                    ];
                }
            } elseif ($type === 'buttons' && isset($comp['buttons'])) {
                foreach ($comp['buttons'] as $index => $button) {
                    if ($button['type'] === 'URL' && isset($button['url'])) {
                        $btnParams = $this->extractParameters($button['url'], $variableMappings, $resolvedValues);
                        if (!empty($btnParams)) {
                            $outboundComponents[] = [
                                'type' => 'button',
                                'sub_type' => 'url',
                                'index' => (string)$index,
                                'parameters' => $btnParams
                            ];
                        }
                    }
                }
            }
        }

        return $outboundComponents;
    }

    /**
     * Extracts variables from a text string and maps them to parameters in the correct order.
     */
    protected function extractParameters(string $text, array $mappings, array $values): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        $foundVars = $matches[1] ?? [];
        $parameters = [];

        foreach ($foundVars as $varName) {
            $value = $values[$varName] ?? '';
            
            // Meta expects text parameters for these placeholders
            $parameters[] = [
                'type' => 'text',
                'text' => (string)$value
            ];
        }

        return $parameters;
    }
}
