<?php

namespace App\Listeners;

use App\Events\Chat\InboundMessageReceived;
use App\Services\Automations\AutomationTriggerService;
use App\Models\AutomationFlow;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

class AutomationEventSubscriber
{
    /**
     * Handle inbound message events.
     */
    public function handleInboundMessage(InboundMessageReceived $event)
    {
        Log::info('TRACE B: AutomationEventSubscriber reached', ['event_company_id' => $event->companyId]);

        $activeFlows = AutomationFlow::where('company_id', $event->companyId)
            ->where('is_enabled', true)
            ->get();

        Log::info('TRACE C: Matching Flows Found', ['count' => count($activeFlows)]);

        foreach ($activeFlows as $flow) {
            Log::info("Processing Matched Flow: [{$flow->id}] {$flow->name}");
            $trigger = $flow->nodes()->where('type', 'trigger')->first();
            
            if (!$trigger) {
                Log::info("Skipping Flow [{$flow->id}]: No trigger node found.");
                continue;
            }

            $currentCategory = $trigger->config['trigger_category'] ?? null;
            if ($currentCategory !== 'event_based') {
                Log::info("Skipping Flow [{$flow->id}]: Trigger category is '{$currentCategory}', expected 'event_based'.", ['node_id' => $trigger->id]);
                continue;
            }

            $defKey = $trigger->config['trigger_definition_key'] ?? $trigger->subtype;
            Log::info("Evaluating Trigger: [Node: {$trigger->id}] [Subtype: {$trigger->subtype}] [DefKey: {$defKey}]");

            // Broaden matching: Trigger if it's the specific WhatsApp event OR a generic webhook/webhook_api 
            // set to listen for everything.
            if ($defKey === 'new_message_received' || in_array($trigger->subtype, ['webhook', 'webhook_api'])) {
                $payload = [
                    'message_id' => $event->messageId,
                    'message_body' => $event->preview, // Alias 1
                    'text' => $event->preview,         // Alias 2
                    'preview' => $event->preview,      // Alias 3
                    'phone_number' => $event->phoneNumber,
                    'sender_name' => $event->senderName,
                    'conversation_id' => $event->conversationId,
                    'company_id' => $event->companyId,
                    'received_at' => $event->createdAt,
                ];

                // Add 'trigger' nested key for consistency with builder's 'trigger.field' notation
                $payload['trigger'] = $payload;

                Log::info("Firing Trigger for Flow [{$flow->id}]", [
                    'company_id' => $event->companyId,
                    'payload_keys' => array_keys($payload)
                ]);

                app(AutomationTriggerService::class)->fireTrigger($trigger, $payload);
            } else {
                Log::info("Skipping Flow [{$flow->id}]: Trigger definition '{$defKey}' does not match.");
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            InboundMessageReceived::class => 'handleInboundMessage',
        ];
    }
}
