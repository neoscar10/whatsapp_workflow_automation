<?php

namespace App\Services\Chat;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\Chat\Conversation;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;
use Exception;

class ChatTemplateSendService
{
    private ChatInboxService $inboxService;
    private ChatMessageService $messageService;
    private \App\Services\WhatsApp\WhatsAppOutboundMessageService $outboundService;

    public function __construct(
        ChatInboxService $inboxService, 
        ChatMessageService $messageService,
        \App\Services\WhatsApp\WhatsAppOutboundMessageService $outboundService
    ) {
        $this->inboxService = $inboxService;
        $this->messageService = $messageService;
        $this->outboundService = $outboundService;
    }

    /**
     * Send a template to a conversation.
     */
    public function sendTemplateToConversation(User $actor, int $conversationId, int $templateId, array $payload = []): array
    {
        $conversation = $this->inboxService->getActiveConversationForUser($actor, $conversationId);
        if (!$conversation) {
            throw new Exception('Conversation not found or access denied.');
        }

        $template = WhatsAppTemplate::where('company_id', $actor->company_id)->find($templateId);
        if (!$template) {
            throw new Exception('Template not found or access denied.');
        }

        $category = strtolower($template->category);
        if (in_array($category, ['utility', 'authentication', 'marketing'])) {
            $billingType = 'template_' . ($category === 'authentication' ? 'auth' : $category);
        } else {
            $billingType = 'template_utility';
        }

        if (!app(\App\Services\Payment\BillingService::class)->canAffordActivity($conversation->company, $billingType)) {
            throw new \App\Exceptions\InsufficientWalletBalanceException("Insufficient wallet balance to send this {$billingType} message.");
        }

        // Resolve body with actual values for local persistence/preview
        $rawComponents = $payload['components'] ?? [];
        $components = $this->normalizeComponents($template, $rawComponents);
        $messageBody = $this->resolveTemplateBody($template, $components);

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'template',
            'body' => $messageBody,
            'status' => 'pending',
            'sent_by_user_id' => $actor->id,
            'sent_at' => now(),
            'meta_payload' => [
                'template_id' => $template->id,
                'template_name' => $template->remote_template_name,
                'language_code' => $template->language_code,
                'components' => $components, // WhatsApp Cloud API payload structure
            ]
        ]);

        // Conversation summary is automatically updated by the ConversationMessage model observer

        // Dispatch the WhatsApp outbound sending logic
        $this->outboundService->sendConversationMessage($message);

        // Broadcast events
        broadcast(new ChatMessageReceived($message));
        broadcast(new ChatConversationUpdated($conversation));

        return [
            'success' => true,
            'message_id' => $message->id,
        ];
    }

    /**
     * Safely extract a scalar string representation from any parameter payload to prevent PHP conversion errors.
     */
    protected function stringifyParam(mixed $param): string
    {
        if ($param === null) {
            return '';
        }

        if (is_scalar($param)) {
            return (string) $param;
        }

        if (is_array($param)) {
            if (isset($param['text'])) {
                return $this->stringifyParam($param['text']);
            }
            if (isset($param['value'])) {
                return $this->stringifyParam($param['value']);
            }
            if (isset($param['payload'])) {
                return $this->stringifyParam($param['payload']);
            }

            $scalars = [];
            foreach ($param as $v) {
                if (is_scalar($v)) {
                    $scalars[] = (string) $v;
                }
            }
            if (!empty($scalars)) {
                return implode(' ', $scalars);
            }
        }

        return '';
    }

    /**
     * Format a single parameter item into standard Meta Cloud API structure.
     */
    protected function formatParameter(mixed $param): array
    {
        if (is_array($param)) {
            if (isset($param['type'])) {
                $type = strtolower((string) $param['type']);
                if (in_array($type, ['image', 'video', 'document', 'location', 'payload'])) {
                    return $param;
                }
                if ($type === 'text') {
                    $textVal = $param['text'] ?? $param['value'] ?? '';
                    return ['type' => 'text', 'text' => $this->stringifyParam($textVal)];
                }
            }

            if (isset($param['text'])) {
                return ['type' => 'text', 'text' => $this->stringifyParam($param['text'])];
            }

            if (isset($param['value'])) {
                return ['type' => 'text', 'text' => $this->stringifyParam($param['value'])];
            }
        }

        return ['type' => 'text', 'text' => $this->stringifyParam($param)];
    }

    /**
     * Normalize components payload to ensure Meta API compliance for body, header, and URL buttons.
     */
    protected function normalizeComponents(WhatsAppTemplate $template, array $components): array
    {
        $normalized = [];

        foreach ($components as $comp) {
            $type = strtolower($comp['type'] ?? 'body');
            
            if ($type === 'body' || $type === 'header') {
                $rawParams = $comp['parameters'] ?? [];
                if (!is_array($rawParams)) {
                    $rawParams = [$rawParams];
                }
                $formattedParams = [];
                foreach ($rawParams as $param) {
                    $formattedParams[] = $this->formatParameter($param);
                }
                if (!empty($formattedParams)) {
                    $normalized[] = [
                        'type' => $type,
                        'parameters' => $formattedParams,
                    ];
                }
            } elseif ($type === 'button') {
                $btnIndex = (string) ($comp['index'] ?? '0');
                $subType = strtolower($comp['sub_type'] ?? 'url');
                $rawParams = $comp['parameters'] ?? [];
                if (!is_array($rawParams)) {
                    $rawParams = [$rawParams];
                }
                $formattedParams = [];
                foreach ($rawParams as $param) {
                    $formattedParams[] = $this->formatParameter($param);
                }
                if (!empty($formattedParams)) {
                    $normalized[] = [
                        'type' => 'button',
                        'sub_type' => $subType,
                        'index' => $btnIndex,
                        'parameters' => $formattedParams,
                    ];
                }
            }
        }

        return $normalized;
    }

    /**
     * Resolve template variable placeholders in body for local storage.
     */
    protected function resolveTemplateBody(WhatsAppTemplate $template, array $components): string
    {
        $body = $template->body_text ?? '';
        
        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'body' && isset($component['parameters']) && is_array($component['parameters'])) {
                foreach ($component['parameters'] as $index => $param) {
                    $placeholder = '{{' . ($index + 1) . '}}';
                    $val = $this->stringifyParam($param);
                    $body = str_replace($placeholder, $val ?: $placeholder, $body);
                }
            }
        }
        
        return $body;
    }
}
