<?php

namespace App\Services\WhatsApp;

use App\Models\Chat\Conversation;
use App\Models\User;
use Illuminate\Support\Carbon;

class WhatsAppTemplateVariableResolver
{
    /**
     * Get list of available system variables with labels and descriptions.
     */
    public function getAvailableSystemVariables(): array
    {
        return [
            ['key' => 'contact_name', 'label' => 'Contact: Full Name', 'description' => 'The full name of the contact.'],
            ['key' => 'contact_phone', 'label' => 'Contact: Phone Number', 'description' => 'The WhatsApp number of the contact.'],
            ['key' => 'agent_name', 'label' => 'Agent: Name', 'description' => 'The name of the agent sending the message.'],
            ['key' => 'company_name', 'label' => 'Company: Name', 'description' => 'The name of your company.'],
            ['key' => 'current_date', 'label' => 'System: Current Date', 'description' => 'Today\'s date (YYYY-MM-DD).'],
            ['key' => 'current_time', 'label' => 'System: Current Time', 'description' => 'The current time (HH:MM).'],
        ];
    }

    /**
     * Resolve a system variable key to its actual value based on context.
     */
    public function resolve(string $key, ?Conversation $conversation = null, ?User $actor = null): string
    {
        return match ($key) {
            'contact_name' => $conversation?->contact_name ?? 'Customer',
            'contact_phone' => $conversation?->contact_phone ?? '',
            'agent_name' => $actor?->name ?? 'Agent',
            'company_name' => $actor?->company?->name ?? 'Our Company',
            'current_date' => Carbon::now()->toDateString(),
            'current_time' => Carbon::now()->format('H:i'),
            default => '',
        };
    }

    /**
     * Resolve all placeholders in a template body for preview purposes.
     */
    public function resolveAllForPreview(string $body, array $mappings, ?Conversation $conversation = null, ?User $actor = null): string
    {
        foreach ($mappings as $placeholder => $config) {
            $value = $this->getValueFromMapping($config, $conversation, $actor);
            $body = str_replace("{{{$placeholder}}}", $value ?: "{{{$placeholder}}}", $body);
        }

        return $body;
    }

    /**
     * Extract value from a specific variable mapping config.
     */
    public function getValueFromMapping(array $config, ?Conversation $conversation = null, ?User $actor = null): string
    {
        $type = $config['type'] ?? 'manual';
        
        if ($type === 'system') {
            return $this->resolve($config['value'] ?? '', $conversation, $actor);
        }

        return $config['value'] ?? '';
    }
}
