<?php

namespace Modules\CA\Services;

use App\Models\Company;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CAReminderActivity;
use Modules\CA\Jobs\DispatchAutomationReminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderSchedulerService
{
    /**
     * Evaluates all active compliance automations and schedules pending reminders.
     */
    public function evaluateAndScheduleReminders(): int
    {
        $activeAutomations = CAClientAutomation::where('is_enabled', true)
            ->where('status', 'active')
            ->with(['rules', 'requirements', 'client'])
            ->get();

        $dispatchedCount = 0;
        $now = Carbon::now();

        foreach ($activeAutomations as $automation) {
            $client = $automation->client;
            if (!$client || $client->status !== 'active') {
                continue;
            }

            foreach ($automation->requirements as $requirement) {
                // If already submitted/completed, skip reminders
                if ($requirement->status === 'completed' || $requirement->status === 'submitted') {
                    continue;
                }

                if (empty($requirement->next_due_date)) {
                    continue;
                }

                $dueDate = Carbon::parse($requirement->next_due_date);

                foreach ($automation->rules as $rule) {
                    if (!$rule->is_enabled) {
                        continue;
                    }

                    // 1. Calculate the rule trigger datetime
                    $triggerTime = Carbon::parse($rule->send_time);
                    $triggerDateTime = $dueDate->copy()
                        ->setTime($triggerTime->hour, $triggerTime->minute, 0);

                    if ($rule->trigger_type === 'before_due') {
                        $triggerDateTime->subDays($rule->offset_days);
                    } elseif ($rule->trigger_type === 'after_due') {
                        $triggerDateTime->addDays($rule->offset_days);
                    }

                    // 2. Check if we are past/within the trigger window, and not already executed
                    // Standard trigger window: check if trigger date is today, and within the current hour range
                    if ($now->greaterThanOrEqualTo($triggerDateTime) && $now->diffInHours($triggerDateTime) < 24) {
                        // Check if reminder was already sent for this cycle (e.g. within 30 days for monthly)
                        $bufferDays = match ($automation->frequency) {
                            'daily' => 1,
                            'weekly' => 6,
                            'quarterly' => 80,
                            'yearly' => 350,
                            default => 25, // monthly
                        };

                        $alreadySent = CAReminderActivity::where('ca_client_automation_id', $automation->id)
                            ->where('ca_client_automation_rule_id', $rule->id)
                            ->where('ca_client_compliance_requirement_id', $requirement->id)
                            ->whereIn('status', ['sent', 'delivered', 'read'])
                            ->where('created_at', '>=', $now->copy()->subDays($bufferDays))
                            ->exists();

                        if (!$alreadySent) {
                            // Schedule / Log activity as scheduled
                            $activity = CAReminderActivity::create([
                                'company_id' => $automation->company_id,
                                'ca_client_automation_id' => $automation->id,
                                'ca_client_automation_rule_id' => $rule->id,
                                'ca_client_compliance_requirement_id' => $requirement->id,
                                'status' => 'scheduled',
                            ]);

                            // Dispatch Job to send WABA message
                            DispatchAutomationReminder::dispatch($activity->id);
                            $dispatchedCount++;
                        }
                    }
                }
            }
        }

        return $dispatchedCount;
    }
}
