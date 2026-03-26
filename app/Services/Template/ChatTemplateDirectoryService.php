<?php

namespace App\Services\Template;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Collection;

class ChatTemplateDirectoryService
{
    /**
     * Get templates eligible for sending in chat for a user's company.
     */
    public function getChatEligibleTemplatesForUser(User $user, array $filters = []): array
    {
        $search = $filters['search'] ?? '';
        $filter = $filters['filter'] ?? 'all';

        $query = WhatsAppTemplate::where('company_id', $user->company_id);

        // Filter by search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('remote_template_name', 'like', "%{$search}%")
                  ->orWhere('display_title', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if ($filter === 'approved') {
            $query->where('status', 'approved');
        } elseif ($filter === 'recent') {
            $query->orderByDesc('last_synced_at'); // Simplified for now, can use usage logs later
        } else {
            $query->orderBy('display_title');
        }

        // Only show approved or pending templates for sending in chat usually
        // But for this phase, we follow the "all/approved/recent" filter
        
        return $query->get()->map(function ($template) {
            return $this->formatTemplateForModal($template);
        })->toArray();
    }

    /**
     * Format template for the selection modal.
     */
    public function formatTemplateForModal(WhatsAppTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->display_title ?? $template->remote_template_name,
            'subtitle' => ucfirst($template->category) . ' • ' . strtoupper($template->language_code),
            'icon' => $this->getIconForCategory($template->category),
            'status' => $template->status,
            'category' => $template->category,
        ];
    }

    /**
     * Get detailed preview data for a template.
     */
    public function getTemplatePreview(User $user, int $templateId): ?array
    {
        $template = WhatsAppTemplate::where('company_id', $user->company_id)->find($templateId);
        if (!$template) {
            return null;
        }

        $meta = $template->meta_payload ?? [];
        $components = $meta['components'] ?? [];
        
        $variables = [];
        $bodyText = $template->body_text;
        $headerText = $template->header_text;
        
        // Extract variables from all relevant components
        foreach ($components as $component) {
            $type = strtolower($component['type']);
            
            if ($type === 'header' && isset($component['text'])) {
                preg_match_all('/\{\{([^}]+)\}\}/', $component['text'], $matches);
                foreach ($matches[1] ?? [] as $var) {
                    $variables[] = ['name' => $var, 'component' => 'header'];
                }
            } elseif ($type === 'body' && isset($component['text'])) {
                preg_match_all('/\{\{([^}]+)\}\}/', $component['text'], $matches);
                foreach ($matches[1] ?? [] as $var) {
                    $variables[] = ['name' => $var, 'component' => 'body'];
                }
            } elseif ($type === 'buttons' && isset($component['buttons'])) {
                foreach ($component['buttons'] as $index => $button) {
                    if (isset($button['url'])) {
                        preg_match_all('/\{\{([^}]+)\}\}/', $button['url'], $matches);
                        foreach ($matches[1] ?? [] as $var) {
                            $variables[] = [
                                'name' => $var, 
                                'component' => 'button', 
                                'sub_type' => 'url', 
                                'index' => $index
                            ];
                        }
                    }
                }
            }
        }

        // De-duplicate while preserving component info (first occurrence wins)
        $uniqueVariables = [];
        $seen = [];
        foreach ($variables as $v) {
            if (!isset($seen[$v['name']])) {
                $uniqueVariables[] = $v;
                $seen[$v['name']] = true;
            }
        }

        // Split body text into paragraphs for cleaner rendering
        $paragraphs = explode("\n", $bodyText);
        
        // Find buttons text
        $buttonText = $template->buttons->first()?->text;

        return [
            'id' => $template->id,
            'name' => $template->display_title ?? $template->remote_template_name,
            'preview_paragraphs' => array_filter(array_map('trim', $paragraphs)),
            'variables' => $uniqueVariables,
            'category_label' => ucfirst($template->category),
            'button_text' => $buttonText,
            'header_text' => $headerText,
            'header_type' => $template->header_type,
            'original_body_text' => $bodyText,
            'time_label' => now()->format('h:i A'),
        ];
    }

    protected function getIconForCategory(?string $category): string
    {
        return match (strtolower($category ?? '')) {
            'marketing' => 'campaign',
            'utility' => 'settings_suggest',
            'authentication' => 'verified_user',
            default => 'description',
        };
    }
}
