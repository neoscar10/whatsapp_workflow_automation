<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientAutomationRule;
use Modules\CA\Events\ReminderRuleCreated;
use Modules\CA\Events\ReminderRuleUpdated;
use Exception;

class ReminderRuleService
{
    /**
     * Validate an array of reminder rules before saving.
     *
     * @param array $rules Array of rule arrays with keys: trigger_type, offset_days, send_time
     * @throws Exception if validation fails
     */
    public function validate(array $rules): void
    {
        $onDueCount = 0;
        $seen = [];

        foreach ($rules as $index => $rule) {
            $trigger    = $rule['trigger_type'] ?? '';
            $offsetDays = (int) ($rule['offset_days'] ?? 0);
            $sendTime   = $rule['send_time'] ?? '';

            // Validate trigger_type
            if (!in_array($trigger, CAClientAutomationRule::ALLOWED_TRIGGERS)) {
                throw new Exception("Invalid trigger type '{$trigger}' at rule #{$index}.");
            }

            // Offset days cannot be negative
            if ($offsetDays < 0) {
                throw new Exception("Offset days cannot be negative at rule #{$index}.");
            }

            // on_due rules must have offset_days = 0
            if ($trigger === CAClientAutomationRule::TRIGGER_ON_DUE && $offsetDays !== 0) {
                throw new Exception("The 'On Due' rule must have 0 offset days.");
            }

            // Only one on_due rule allowed
            if ($trigger === CAClientAutomationRule::TRIGGER_ON_DUE) {
                $onDueCount++;
                if ($onDueCount > 1) {
                    throw new Exception('Only one "On Due" reminder rule is allowed per automation.');
                }
            }

            // Send time is required
            if (empty(trim($sendTime))) {
                throw new Exception("Send time is required at rule #{$index}.");
            }

            // No duplicate trigger + offset combinations
            $key = "{$trigger}:{$offsetDays}";
            if (in_array($key, $seen)) {
                throw new Exception("Duplicate reminder rule: {$trigger} with {$offsetDays} days offset.");
            }
            $seen[] = $key;
        }
    }

    /**
     * Persist validated rules for a client automation.
     *
     * Deletes existing rules and re-creates the full set (simple replace strategy).
     *
     * @param int   $clientAutomationId
     * @param array $rules
     * @return CAClientAutomationRule[]
     */
    public function saveRules(int $clientAutomationId, array $rules): array
    {
        $this->validate($rules);

        // Delete existing rules for clean replace
        CAClientAutomationRule::where('client_automation_id', $clientAutomationId)->delete();

        $saved = [];
        foreach ($rules as $sequence => $rule) {
            $record = CAClientAutomationRule::create([
                'client_automation_id' => $clientAutomationId,
                'trigger_type'         => $rule['trigger_type'],
                'offset_days'          => (int) ($rule['offset_days'] ?? 0),
                'send_time'            => $rule['send_time'],
                'sequence'             => $sequence,
                'is_enabled'           => (bool) ($rule['is_enabled'] ?? true),
            ]);

            ReminderRuleCreated::dispatch($record);
            $saved[] = $record;
        }

        return $saved;
    }

    /**
     * Return default reminder rules for a given frequency.
     * Used when no rules have been configured yet.
     */
    public function getDefaultRules(string $frequency): array
    {
        return match ($frequency) {
            'daily'  => [
                ['trigger_type' => 'on_due',     'offset_days' => 0, 'send_time' => '09:00', 'is_enabled' => true],
            ],
            'weekly' => [
                ['trigger_type' => 'before_due', 'offset_days' => 2, 'send_time' => '09:00', 'is_enabled' => true],
                ['trigger_type' => 'on_due',     'offset_days' => 0, 'send_time' => '09:00', 'is_enabled' => true],
            ],
            default  => [
                ['trigger_type' => 'before_due', 'offset_days' => 7, 'send_time' => '09:00', 'is_enabled' => true],
                ['trigger_type' => 'before_due', 'offset_days' => 3, 'send_time' => '09:00', 'is_enabled' => true],
                ['trigger_type' => 'before_due', 'offset_days' => 1, 'send_time' => '09:00', 'is_enabled' => true],
                ['trigger_type' => 'on_due',     'offset_days' => 0, 'send_time' => '09:00', 'is_enabled' => true],
                ['trigger_type' => 'after_due',  'offset_days' => 2, 'send_time' => '10:00', 'is_enabled' => true],
            ],
        };
    }
}
