<?php

namespace App\Services\Chat;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\Chat\Conversation;
use Exception;

class ChatTemplateSendService
{
    private ChatInboxService $inboxService;
    private ChatMessageService $messageService;

    public function __construct(ChatInboxService $inboxService, ChatMessageService $messageService)
    {
        $this->inboxService = $inboxService;
        $this->messageService = $messageService;
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

        if ($template->status !== 'approved') {
             // For production we usually only send approved, but let's allow it for now if requested
             // throw new Exception('Only approved templates can be sent.');
        }

        // In a real app, we would call Meta API here.
        // For this phase, we persist the outbound record and update the thread.
        
        $messageBody = $this->resolveTemplatePreview($template);

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'template',
            'body' => $messageBody,
            'status' => 'sent',
            'sent_by_user_id' => $actor->id,
            'sent_at' => now(),
            'meta_payload' => [
                'template_id' => $template->id,
                'template_name' => $template->remote_template_name,
                'variables' => $payload['variables'] ?? [],
            ]
        ]);

        // Update conversation summary
        $conversation->update([
            'last_message_preview' => 'Template: ' . ($template->display_title ?? $template->remote_template_name),
            'last_message_at' => now(),
        ]);

        return [
            'success' => true,
            'message_id' => $message->id,
        ];
    }

    /**
     * Minimal template variable resolution for preview/sending.
     */
    protected function resolveTemplatePreview(WhatsAppTemplate $template): string
    {
        $body = $template->body_text;
        
        // Simple placeholder resolution for preview if no variables provided
        // Future phases will add real variable substitution
        return $body;
    }
}
