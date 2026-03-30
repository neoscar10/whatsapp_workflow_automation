<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessBehavioralAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automations:process-behavior';
    protected $description = 'Scan and fire behavior-based automation triggers (e.g. Inactivity).';

    public function handle()
    {
        $this->info("Scanning for behavioral triggers...");

        $activeFlows = \App\Models\AutomationFlow::where('is_enabled', true)->get();

        foreach ($activeFlows as $flow) {
            $trigger = $flow->nodes()->where('type', 'trigger')->first();
            
            if (!$trigger || ($trigger->config['trigger_category'] ?? null) !== 'behavior_based') {
                continue;
            }

            $this->processTrigger($trigger);
        }

        $this->info("Scan complete.");
    }

    protected function processTrigger($node)
    {
        $defKey = $node->config['trigger_definition_key'] ?? $node->subtype;

        if ($defKey === 'inactive_user_24h') {
            $this->processInactivity($node, 24);
        }
    }

    protected function processInactivity($node, $hours)
    {
        $threshold = now()->subHours($hours);
        $windowStart = $threshold->copy()->subMinutes(10); // 10 min window to catch them

        // Find conversations where last customer message was exactly within the window
        // and no outbound message has been sent since then.
        $conversations = \Illuminate\Support\Facades\DB::table('conversations')
            ->where('company_id', $node->flow->company_id)
            ->whereBetween('last_customer_message_at', [$windowStart, $threshold])
            ->get();

        foreach ($conversations as $conv) {
            // Check if we already fired for this conversation today to avoid spam
            // (Real implementation would store 'last_behavior_fire' in metadata or a separate table)
            
            $this->info("Firing inactivity trigger for conversation [{$conv->id}]");

            app(\App\Services\Automations\AutomationTriggerService::class)->fireTrigger($node, [
                'conversation_id' => $conv->id,
                'last_interaction_at' => $conv->last_customer_message_at,
                'customer_phone' => $conv->contact_phone ?? '',
            ]);
        }
    }
}
