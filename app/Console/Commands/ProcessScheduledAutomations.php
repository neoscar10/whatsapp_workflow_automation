<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessScheduledAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automations:process-scheduled';
    protected $description = 'Scan and fire active time-based automation triggers.';

    public function handle()
    {
        $this->info("Scanning for scheduled automations...");

        $activeFlows = \App\Models\AutomationFlow::where('is_enabled', true)->get();

        foreach ($activeFlows as $flow) {
            $trigger = $flow->nodes()->where('type', 'trigger')->first();
            
            if (!$trigger || ($trigger->config['trigger_category'] ?? null) !== 'time_based') {
                continue;
            }

            if ($this->isTriggerDue($trigger)) {
                $this->info("Firing scheduled automation [{$flow->id}]: {$flow->name}");
                
                app(\App\Services\Automations\AutomationTriggerService::class)->fireTrigger($trigger, [
                    'trigger_time' => now()->toDateTimeString(),
                    'scheduled_time' => $trigger->config['start_time'] ?? '00:00',
                ]);

                // Update last run info in node config to prevent double firing
                $config = $trigger->config;
                $config['last_fired_at'] = now()->toDateTimeString();
                $trigger->config = $config;
                $trigger->save();
            }
        }

        $this->info("Scan complete.");
    }

    protected function isTriggerDue($node): bool
    {
        $config = $node->config ?? [];
        $interval = $config['repeat_interval'] ?? 'once';
        $startTime = $config['start_time'] ?? '00:00'; // HH:MM
        $lastFired = $config['last_fired_at'] ?? null;

        $now = now();
        $todayTime = $now->format('H:i');

        // Simple check: if current time >= start time and (never fired today or never fired at all)
        if ($todayTime >= $startTime) {
            if (!$lastFired) return true;

            $lastFiredDate = \Illuminate\Support\Carbon::parse($lastFired);
            
            return match($interval) {
                'once' => false, // Already fired once
                'daily' => !$lastFiredDate->isToday(),
                'weekly' => !$lastFiredDate->isToday() && $now->dayOfWeek === 1, // Simple Monday check
                'monthly' => !$lastFiredDate->isToday() && $now->day === 1,
                default => false,
            };
        }

        return false;
    }
}
