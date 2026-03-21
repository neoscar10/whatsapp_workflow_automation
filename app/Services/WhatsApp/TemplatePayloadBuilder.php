<?php

namespace App\Services\WhatsApp;

class TemplatePayloadBuilder
{
    /**
     * Builds the main payload required by Meta Graph API for creating/updating templates.
     */
    public function build(array $data, array $buttons = []): array
    {
        $components = [];

        // Header Component
        if (!empty($data['header_type']) && $data['header_type'] !== 'none') {
            $headerComponent = [
                'type' => 'HEADER',
                'format' => strtoupper($data['header_type']), // TEXT, IMAGE, VIDEO, DOCUMENT
            ];

            if ($data['header_type'] === 'text' && !empty($data['header_text'])) {
                $headerComponent['text'] = $data['header_text'];
                // Check for variables in header text (e.g. {{1}})
                if (preg_match('/\{\{1\}\}/', $data['header_text'])) {
                     $headerComponent['example'] = [
                         'header_text' => [
                             $data['example_payload']['header_text'][0] ?? 'Example Header'
                         ]
                     ];
                }
            } elseif (in_array($data['header_type'], ['image', 'video', 'document'])) {
                 // For creation, media headers only need a format, but optionally example handles.
                 // We will supply a basic example structure if needed by Meta.
                 if (isset($data['example_payload']['header_handle'][0])) {
                     $headerComponent['example'] = [
                         'header_handle' => [
                             $data['example_payload']['header_handle'][0]
                         ]
                     ];
                 }
            }
            $components[] = $headerComponent;
        }

        // Body Component
        if (!empty($data['body_text'])) {
            $bodyComponent = [
                'type' => 'BODY',
                'text' => $data['body_text'],
            ];

             // Check for variables in body text (e.g. {{1}}, {{2}})
             $varCount = preg_match_all('/\{\{\d+\}\}/', $data['body_text']);
             if ($varCount > 0) {
                 $exampleBodyText = [];
                 for ($i = 1; $i <= $varCount; $i++) {
                     $exampleBodyText[] = $data['example_payload']['body_text'][$i-1] ?? "Example Value {$i}";
                 }
                 $bodyComponent['example'] = [
                     'body_text' => [$exampleBodyText] // Note: Meta expects an array of arrays for body_text examples
                 ];
             }

            $components[] = $bodyComponent;
        }

        // Footer Component
        if (!empty($data['footer_text'])) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $data['footer_text'],
            ];
        }

        // Buttons Component
        if (!empty($buttons)) {
            $buttonComponent = [
                'type' => 'BUTTONS',
                'buttons' => [],
            ];

            foreach ($buttons as $button) {
                if ($button['type'] === 'quick_reply') {
                    $buttonComponent['buttons'][] = [
                        'type' => 'QUICK_REPLY',
                        'text' => $button['text'],
                    ];
                } elseif ($button['type'] === 'url') {
                     $btn = [
                        'type' => 'URL',
                        'text' => $button['text'],
                        'url' => $button['url'],
                    ];
                     // Check if URL has a variable (e.g., https://example.com/{{1}})
                     if (str_contains($button['url'], '{{1}}')) {
                          $btn['example'] = [
                              $button['example_value'] ?? 'example_path'
                          ];
                     }
                    $buttonComponent['buttons'][] = $btn;
                } elseif ($button['type'] === 'phone_number') {
                    $buttonComponent['buttons'][] = [
                        'type' => 'PHONE_NUMBER',
                        'text' => $button['text'],
                        'phone_number' => $button['phone_number'],
                    ];
                }
            }
            $components[] = $buttonComponent;
        }

        return [
            'name' => $data['remote_template_name'],
            'language' => $data['language_code'],
            'category' => strtoupper($data['category']), // MARKETING, UTILITY, AUTHENTICATION
            'components' => $components,
        ];
    }
}
