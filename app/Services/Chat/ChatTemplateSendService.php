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

        // Resolve body with actual values for local persistence/preview
        $components = $payload['components'] ?? [];
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

        // Update conversation summary
        $conversation->update([
            'last_message_preview' => 'Template: ' . ($template->display_title ?? $template->remote_template_name),
            'last_message_at' => now(),
        ]);

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
     * Resolve template variable placeholders in body for local storage.
     */
    protected function resolveTemplateBody(WhatsAppTemplate $template, array $components): string
    {
        $body = $template->body_text;
        
        foreach ($components as $component) {
            if ($component['type'] === 'body' && isset($component['parameters'])) {
                foreach ($component['parameters'] as $index => $param) {
                    $placeholder = '{{' . ($index + 1) . '}}';
                    $body = str_replace($placeholder, $param['text'] ?? $placeholder, $body);
                }
            }
        }
        
        return $body;
    }
}
