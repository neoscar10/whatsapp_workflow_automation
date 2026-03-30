<?php

namespace App\Listeners;

use App\Events\Chat\InboundMessageReceived;
use App\Services\Automations\AutomationTriggerService;
use App\Models\AutomationFlow;
use Illuminate\Events\Dispatcher;

class AutomationEventSubscriber
{
    /**
     * Handle inbound message events.
     */
    public function handleInboundMessage(InboundMessageReceived $event)
    {
        $activeFlows = AutomationFlow::where('company_id', $event->companyId)
            ->where('is_enabled', true)
            ->get();

        foreach ($activeFlows as $flow) {
            $trigger = $flow->nodes()->where('type', 'trigger')->first();
            
            if (!$trigger || ($trigger->config['trigger_category'] ?? null) !== 'event_based') {
                continue;
            }

            $defKey = $trigger->config['trigger_definition_key'] ?? $trigger->subtype;

            if ($defKey === 'new_message_received') {
                app(AutomationTriggerService::class)->fireTrigger($trigger, [
                    'message_id' => $event->messageId,
                    'message_body' => $event->preview,
                    'conversation_id' => $event->conversationId,
                    'company_id' => $event->companyId,
                    'received_at' => $event->createdAt,
                ]);
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
