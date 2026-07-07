<?php

namespace Modules\CA\Services;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use Modules\CA\Models\CAReminderActivity;
use Illuminate\Support\Facades\Log;
use Exception;

use App\Models\WhatsApp\WhatsAppTemplate;

class AutomationExecutionService
{
    public function __construct(
        private VariableResolverService $variableResolverService,
        private WhatsAppOutboundMessageService $outboundMessageService
    ) {}

    /**
     * Executes sending a single scheduled compliance reminder message.
     */
    public function executeReminder(int $activityId): bool
    {
        $activity = CAReminderActivity::find($activityId);
        if (!$activity) {
            Log::error("AutomationExecutionService: Activity log ID {$activityId} not found.");
            return false;
        }

        $activity->update(['status' => 'queued']);

        $automation = $activity->clientAutomation;
        $requirement = $activity->requirement;
        $client = $automation?->client;

        if (!$automation || !$requirement || !$client) {
            $err = "Required compliance context missing for reminder execution.";
            $activity->update(['status' => 'failed', 'error_message' => $err]);
            return false;
        }

        // 1. Resolve WhatsApp Account and Outbound Phone Number
        $account = WhatsAppAccount::where('company_id', $automation->company_id)
            ->where('connection_status', 'connected')
            ->first();

        if (!$account) {
            $err = "No active WABA account connected for company ID {$automation->company_id}.";
            $activity->update(['status' => 'failed', 'error_message' => $err]);
            return false;
        }

        $localNumber = WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)->first();
        if (!$localNumber) {
            $err = "No phone number associated with WABA account ID {$account->id}.";
            $activity->update(['status' => 'failed', 'error_message' => $err]);
            return false;
        }

        // 2. Resolve Template (must be approved/active or we skip sending)
        $isOverdue = $activity->rule?->trigger_type === 'after_due';
        if ($isOverdue) {
            $overdueTemplateId = $automation->metadata_json['whatsapp_overdue_template_id'] ?? null;
            $template = $overdueTemplateId ? WhatsAppTemplate::find($overdueTemplateId) : null;
            if (!$template) {
                $templateManagementService = app(TemplateManagementService::class);
                $template = $templateManagementService->resolveOverdueTemplateForAutomation($automation);
            }
        } else {
            $template = $automation->whatsappTemplate;
            if (!$template) {
                $templateManagementService = app(TemplateManagementService::class);
                $template = $templateManagementService->resolveTemplateForAutomation($automation);
            }
        }

        if (!$template) {
            $err = "No template exists for automation library ID {$automation->automation_library_id}.";
            $activity->update(['status' => 'failed', 'error_message' => $err]);
            return false;
        }

        $templateStatus = strtolower($template->status);
        if ($templateStatus !== 'approved' && $templateStatus !== 'active') {
            $err = "Template {$template->remote_template_name} is in status '{$templateStatus}' (must be 'approved' or 'active').";
            $activity->update(['status' => 'failed', 'error_message' => $err]);
            return false;
        }

        try {
            // 3. Resolve Variables & Build Meta Parameters payload using customized variable mappings
            $metaComponents = $this->variableResolverService->resolveMetaComponentsForAutomation(
                $automation,
                $requirement,
                $client,
                $client->company
            );

            // 4. Resolve/Create Conversation
            $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone);
            if (!str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 10) {
                $cleanPhone = '91' . $cleanPhone;
            }

            $conversation = Conversation::firstOrCreate(
                [
                    'company_id'              => $automation->company_id,
                    'whatsapp_phone_number_id'=> $localNumber->id,
                    'contact_phone'           => $cleanPhone,
                ],
                [
                    'contact_name'            => $client->client_name,
                    'status'                  => 'open',
                    'contact_id'              => $client->contact_id,
                ]
            );

            if ($client->contact_id && $conversation->contact_id !== $client->contact_id) {
                $conversation->update(['contact_id' => $client->contact_id]);
            }

            // 5. Create Outbound Template Message in DB
            $message = ConversationMessage::create([
                'conversation_id'     => $conversation->id,
                'direction'           => 'outbound',
                'message_type'        => 'template',
                'body'                => $template->remote_template_name,
                'status'              => 'queued',
                'meta_payload'        => [
                    'template_name' => $template->remote_template_name,
                    'language_code' => $template->language_code ?? 'en_US',
                    'components'    => $metaComponents,
                ],
            ]);

            // 6. Send the message via Outbound Message Service
            $success = $this->outboundMessageService->sendConversationMessage($message);

            if ($success && $message->status === 'sent') {
                $activity->update([
                    'status'              => 'sent',
                    'external_message_id' => $message->external_message_id,
                    'sent_at'             => now(),
                    'response_payload'    => $message->meta_payload,
                ]);
                return true;
            } else {
                $err = $message->meta_payload['error'] ?? "Meta API outbound template dispatch failed.";
                $activity->update([
                    'status'        => 'failed',
                    'error_message' => $err,
                    'response_payload' => $message->meta_payload,
                ]);
                return false;
            }

        } catch (Exception $e) {
            $activity->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Reminder execution exception: " . $e->getMessage());
            return false;
        }
    }
}
