<?php

namespace App\Services\Template;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Collection;

class ChatTemplateDirectoryService
{
    protected function resolveCompanyId(User $user): int
    {
        if ($user->company_id) {
            return (int) $user->company_id;
        }

        if ($user->role === 'super_admin' || ($user->is_super_admin ?? false) || !$user->company_id) {
            return (int) (\App\Models\Company::where('status', 'active')->value('id') ?? 1);
        }

        return 1;
    }

    /**
     * Get templates eligible for sending in chat for a user's company.
     */
    public function getChatEligibleTemplatesForUser(User $user, array $filters = []): array
    {
        $search = $filters['search'] ?? '';
        $filter = $filters['filter'] ?? 'all';

        $companyId = $this->resolveCompanyId($user);

        $query = WhatsAppTemplate::where('company_id', $companyId)
            ->whereNotIn('status', ['rejected', 'REJECTED']);

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
        }

        if ($filter === 'recent') {
            $query->orderByDesc('is_default')->orderByDesc('last_synced_at');
        } else {
            $query->orderByDesc('is_default')->orderBy('display_title');
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
            'is_default' => (bool) $template->is_default,
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

        // Split body text into paragraphs for cleaner rendering
        $paragraphs = explode("\n", $template->body_text);
        
        $variables = [];
        
        // Extract Header variables
        if ($template->header_text) {
            preg_match_all('/\{\{([^}]+)\}\}/', $template->header_text, $hMatches);
            foreach (array_unique($hMatches[1] ?? []) as $var) {
                $variables[] = ['name' => $var, 'component' => 'header'];
            }
        }

        // Extract Body variables
        preg_match_all('/\{\{([^}]+)\}\}/', $template->body_text, $bMatches);
        foreach (array_unique($bMatches[1] ?? []) as $var) {
            $variables[] = ['name' => $var, 'component' => 'body'];
        }

        // Extract Button variables (Dynamic URLs)
        foreach ($template->buttons as $btnIndex => $button) {
            $isUrl = strtoupper($button->type ?? '') === 'URL';
            $url = $button->url ?? '';
            if ($isUrl && (str_contains($url, '{{1}}') || str_contains($url, '{{ 1 }}') || str_contains($url, '%7B%7B1%7D%7D') || preg_match('/\{\{\d+\}\}/', $url))) {
                $variables[] = [
                    'name' => "Button ({$button->text}) Link Variable",
                    'component' => 'button',
                    'button_index' => $btnIndex,
                    'var_index' => 1,
                ];
            }
        }

        // Find buttons text
        $buttonText = $template->buttons->first()?->text;

        return [
            'id' => $template->id,
            'name' => $template->display_title ?? $template->remote_template_name,
            'header_type' => $template->header_type,
            'header_text' => $template->header_text,
            'preview_paragraphs' => array_filter(array_map('trim', $paragraphs)),
            'variables' => $variables, // Array of ['name' => '...', 'component' => '...']
            'category_label' => ucfirst($template->category),
            'button_text' => $buttonText,
            'original_body_text' => $template->body_text,
            'original_header_text' => $template->header_text,
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
