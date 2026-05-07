<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Models\Contact\Contact;
use Illuminate\Support\Str;

class CampaignTemplateVariableService
{
    /**
     * Provide available personalization fields for mapping UI.
     */
    public function provideAvailablePersonalizationFields(): array
    {
        return [
            ['key' => 'contact.name', 'label' => 'Contact: Full Name'],
            ['key' => 'contact.first_name', 'label' => 'Contact: First Name'],
            ['key' => 'contact.last_name', 'label' => 'Contact: Last Name'],
            ['key' => 'contact.phone', 'label' => 'Contact: Phone'],
            ['key' => 'contact.email', 'label' => 'Contact: Email'],
            ['key' => 'static', 'label' => 'Static Value'],
        ];
    }

    /**
     * Build the WhatsApp Cloud API component payload for a specific recipient.
     */
    public function buildRecipientPayload(Campaign $campaign, CampaignRecipient $recipient): array
    {
        $mapping = $campaign->template_variable_mapping ?? [];
        $defaults = $campaign->default_variable_values ?? [];
        $personalization = $recipient->personalization_data ?? [];
        
        $contact = $recipient->contact;
        
        $components = [];
        
        // WhatsApp templates usually have 'header', 'body', 'button' components
        // For simplicity, we focus on 'body' and 'header' variables
        
        $bodyParams = $this->resolveParamsForComponent('body', $mapping, $defaults, $personalization, $contact);
        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParams
            ];
        }

        $headerParams = $this->resolveParamsForComponent('header', $mapping, $defaults, $personalization, $contact);
        if (!empty($headerParams)) {
            $components[] = [
                'type' => 'header',
                'parameters' => $headerParams
            ];
        }

        return $components;
    }

    /**
     * Resolve parameters for a specific component type.
     */
    protected function resolveParamsForComponent(string $type, array $mapping, array $defaults, array $personalization, ?Contact $contact): array
    {
        $params = [];
        
        // Mapping structure expected:
        // [
        //   "body" => [
        //      "1" => ["source" => "contact.first_name", "fallback" => "there"],
        //      "2" => ["source" => "static", "value" => "Gold"]
        //   ]
        // ]
        
        if (!isset($mapping[$type])) {
            return [];
        }

        foreach ($mapping[$type] as $index => $config) {
            $value = $this->resolveValue($config, $personalization, $contact);
            
            if (empty($value) && isset($config['fallback'])) {
                $value = $config['fallback'];
            }

            if (empty($value) && isset($defaults[$type][$index])) {
                $value = $defaults[$type][$index];
            }

            $params[] = [
                'type' => 'text',
                'text' => (string)($value ?? '')
            ];
        }

        return $params;
    }

    /**
     * Resolve a single value based on mapping config.
     */
    protected function resolveValue(array $config, array $personalization, ?Contact $contact): ?string
    {
        $source = $config['source'] ?? 'static';

        if ($source === 'static') {
            return $config['value'] ?? null;
        }

        if (Str::startsWith($source, 'contact.')) {
            if (!$contact) return null;
            $field = Str::after($source, 'contact.');
            
            return match($field) {
                'name' => $contact->name,
                'first_name' => $this->getFirstName($contact->name),
                'last_name' => $this->getLastName($contact->name),
                'phone' => $contact->phone,
                'email' => $contact->email,
                default => $contact->custom_fields[$field] ?? null,
            };
        }

        if (Str::startsWith($source, 'imported.')) {
            $field = Str::after($source, 'imported.');
            return $personalization[$field] ?? null;
        }

        return null;
    }

    protected function getFirstName(?string $name): string
    {
        if (empty($name)) return '';
        return explode(' ', $name)[0];
    }

    protected function getLastName(?string $name): string
    {
        if (empty($name)) return '';
        $parts = explode(' ', $name);
        return count($parts) > 1 ? end($parts) : '';
    }

    /**
     * Extract variable placeholders from template text components.
     */
    public function extractVariables(\App\Models\WhatsApp\WhatsAppTemplate $template): array
    {
        $variables = [];

        // Body
        preg_match_all('/{{(\d+)}}/', $template->body_text, $bodyMatches);
        if (!empty($bodyMatches[1])) {
            foreach ($bodyMatches[1] as $index) {
                $variables[] = [
                    'key' => $index,
                    'component' => 'body',
                    'example' => $template->example_payload['body'][$index] ?? "Value for {{ {$index} }}",
                ];
            }
        }

        // Header
        if ($template->header_type === 'text' && !empty($template->header_text)) {
            preg_match_all('/{{(\d+)}}/', $template->header_text, $headerMatches);
            if (!empty($headerMatches[1])) {
                foreach ($headerMatches[1] as $index) {
                    $variables[] = [
                        'key' => $index,
                        'component' => 'header',
                        'example' => $template->example_payload['header'][$index] ?? "Header value for {{ {$index} }}",
                    ];
                }
            }
        }

        return $variables;
    }
}
