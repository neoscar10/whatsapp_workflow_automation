<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Jobs\Campaign\DispatchCampaignJob;
use App\Jobs\Campaign\SendCampaignRecipientJob;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use Exception;
use Illuminate\Support\Facades\Log;

class CampaignDispatchService
{
    public function __construct(
        protected WhatsAppOutboundMessageService $outboundService,
        protected CampaignTemplateVariableService $variableService
    ) {}

    /**
     * Dispatch a campaign for sending.
     */
    public function dispatchCampaign(Campaign $campaign): void
    {
        if ($campaign->status === 'cancelled' || $campaign->status === 'completed') {
            return;
        }

        // Enforce that only recipients who pass validation are kept as pending
        $this->applyPreDispatchValidationFilter($campaign);

        $campaign->update([
            'status' => 'sending',
            'started_at' => $campaign->started_at ?? now(),
            'last_dispatched_at' => now(),
        ]);

        // Chunk pending recipients to avoid memory issues
        $campaign->recipients()->where('status', 'pending')
            ->chunkById(100, function ($recipients) {
                foreach ($recipients as $recipient) {
                    $recipient->markQueued();
                    if (config('queue.default') === 'sync') {
                        SendCampaignRecipientJob::dispatchSync($recipient->id);
                    } else {
                        SendCampaignRecipientJob::dispatch($recipient->id);
                    }
                }
            });
    }

    /**
     * Pre-dispatch validation sweep to skip any invalid or 24h-excluded recipients.
     */
    protected function applyPreDispatchValidationFilter(Campaign $campaign): void
    {
        $user = $campaign->company?->users()?->first() ?? auth()->user();
        if (!$user) return;

        $preview = app(CampaignAudienceService::class)->validateAndPreviewRecipients($user, $campaign);
        foreach (($preview['rows'] ?? []) as $row) {
            if (empty($row['is_valid'])) {
                CampaignRecipient::where('id', $row['id'])
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'skipped',
                        'skip_reason' => $row['error_reason'] ?? 'Failed pre-dispatch validation',
                    ]);
            }
        }
        app(CampaignService::class)->recalculateStats($campaign);
    }

    /**
     * Send a single recipient.
     */
    public function sendRecipient(CampaignRecipient $recipient): void
    {
        $campaign = $recipient->campaign;

        // Stop if campaign is no longer active
        if (in_array($campaign->status, ['paused', 'cancelled', 'completed'])) {
            $recipient->update(['status' => 'cancelled']);
            return;
        }

        $recipient->markSending();

        try {
            $result = $this->executeSend($campaign, $recipient);

            if ($result['success']) {
                $recipient->markSent($result['message_id'] ?? null);
            } else {
                $recipient->markFailed(
                    $result['error_code'] ?? 'UNKNOWN_ERROR',
                    $result['error_message'] ?? 'Unknown error occurred during send.',
                    $result['payload'] ?? null
                );
            }
        } catch (Exception $e) {
            Log::error("Campaign send exception for recipient #{$recipient->id}: " . $e->getMessage());
            $recipient->markFailed('EXCEPTION', $e->getMessage());
        }

        // Always increment attempts and update campaign stats
        $recipient->increment('attempts');
        app(CampaignService::class)->recalculateStats($campaign);
    }

    /**
     * Execute the actual send via WhatsApp service.
     */
    protected function executeSend(Campaign $campaign, CampaignRecipient $recipient): array
    {
        if ($campaign->type === 'template') {
            return $this->sendTemplate($campaign, $recipient);
        }

        return $this->sendText($campaign, $recipient);
    }

    /**
     * Send a template message.
     */
    protected function sendTemplate(Campaign $campaign, CampaignRecipient $recipient): array
    {
        $components = $this->variableService->buildRecipientPayload($campaign, $recipient);
        
        // We need to create a ConversationMessage to use the existing WhatsAppOutboundMessageService
        // or interface directly with GraphClient. 
        // Given existing architecture, creating a message is safer for tracking in Chat Inbox.
        
        $conversation = $recipient->contact->conversations()
            ->where('whatsapp_phone_number_id', $campaign->whatsapp_phone_number_id)
            ->first();

        if (!$conversation) {
            // Need a resolver or inbox service to create conversation
            // For now, assume we can create a basic one if missing
            $conversation = $recipient->contact->conversations()->create([
                'company_id' => $campaign->company_id,
                'whatsapp_phone_number_id' => $campaign->whatsapp_phone_number_id,
                'contact_name' => $recipient->name,
                'contact_phone' => $recipient->phone,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $messageBody = $this->resolveTemplateBody($campaign, $components);

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'template',
            'body' => $messageBody,
            'status' => 'pending',
            'meta_payload' => [
                'campaign_id' => $campaign->id,
                'campaign_recipient_id' => $recipient->id,
                'template_name' => $campaign->template_name,
                'language_code' => $campaign->template_language,
                'components' => $components,
            ]
        ]);

        $recipient->update([
            'conversation_id' => $conversation->id,
            'conversation_message_id' => $message->id,
            'resolved_template_payload' => $components,
        ]);

        $success = $this->outboundService->sendConversationMessage($message);

        if ($success) {
            return [
                'success' => true,
                'message_id' => $message->external_message_id
            ];
        }

        return [
            'success' => false,
            'error_code' => 'META_API_ERROR',
            'error_message' => $message->meta_payload['error'] ?? 'API failed',
            'payload' => $message->meta_payload
        ];
    }

    /**
     * Send a text message.
     */
    protected function sendText(Campaign $campaign, CampaignRecipient $recipient): array
    {
        $company = $campaign->company;

        // Find or create conversation for recipient
        $conversation = Conversation::where('company_id', $company->id)
            ->where('contact_phone', $recipient->phone)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'company_id' => $company->id,
                'whatsapp_phone_number_id' => $campaign->whatsapp_phone_number_id,
                'contact_name' => $recipient->name,
                'contact_phone' => $recipient->phone,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $body = $campaign->message_body ?: '';
        if (!empty($recipient->name)) {
            $body = str_replace('{{name}}', $recipient->name, $body);
            $body = str_replace('{{ name }}', $recipient->name, $body);
        }

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $body,
            'status' => 'pending',
            'meta_payload' => [
                'campaign_id' => $campaign->id,
                'campaign_recipient_id' => $recipient->id,
            ]
        ]);

        $recipient->update([
            'conversation_id' => $conversation->id,
            'conversation_message_id' => $message->id,
        ]);

        $success = $this->outboundService->sendConversationMessage($message);

        if ($success) {
            return [
                'success' => true,
                'message_id' => $message->external_message_id
            ];
        }

        $errorMsg = $message->meta_payload['error'] ?? 'Text message sending failed via WhatsApp Meta API.';
        return [
            'success' => false,
            'error_code' => 'TEXT_SEND_FAILED',
            'error_message' => $errorMsg,
            'payload' => $message->meta_payload
        ];
    }

    /**
     * Retry failed recipients.
     */
    public function retryFailed(Campaign $campaign): void
    {
        $campaign->recipients()->where('status', 'failed')
            ->update(['status' => 'pending']);
            
        $this->dispatchCampaign($campaign);
    }

    /**
     * Retry a single recipient.
     */
    public function retryRecipient(CampaignRecipient $recipient): void
    {
        $recipient->update([
            'status' => 'pending',
            'skip_reason' => null,
            'meta_error_code' => null,
            'meta_error_message' => null,
        ]);

        if (config('queue.default') === 'sync') {
            SendCampaignRecipientJob::dispatchSync($recipient->id);
        } else {
            SendCampaignRecipientJob::dispatch($recipient->id);
        }
    }

    /**
     * Resolve template variable placeholders in body for local storage and preview.
     */
    protected function resolveTemplateBody(Campaign $campaign, array $components): string
    {
        $template = $campaign->whatsappTemplate;
        $body = $template?->body_text ?? $campaign->template_name;

        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'body' && isset($component['parameters'])) {
                foreach ($component['parameters'] as $index => $param) {
                    $placeholder = '{{' . ($index + 1) . '}}';
                    $body = str_replace($placeholder, $param['text'] ?? $placeholder, $body);
                }
            }
        }

        return $body;
    }
}
