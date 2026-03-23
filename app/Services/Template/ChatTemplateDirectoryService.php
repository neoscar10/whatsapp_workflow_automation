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

        // Split body text into paragraphs for cleaner rendering
        $paragraphs = explode("\n", $template->body_text);
        
        // Simpler variable extraction (positional {{1}} or named {{name}})
        preg_match_all('/\{\{([^}]+)\}\}/', $template->body_text, $matches);
        $variables = array_unique($matches[1] ?? []);

        // Find buttons text
        $buttonText = $template->buttons->first()?->text;

        return [
            'id' => $template->id,
            'name' => $template->display_title ?? $template->remote_template_name,
            'preview_paragraphs' => array_filter(array_map('trim', $paragraphs)),
            'variables' => $variables,
            'category_label' => ucfirst($template->category),
            'button_text' => $buttonText,
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
