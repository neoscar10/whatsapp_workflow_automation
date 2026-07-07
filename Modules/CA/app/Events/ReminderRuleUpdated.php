<?php

namespace Modules\CA\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClientAutomationRule;

class ReminderRuleUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CAClientAutomationRule $rule
    ) {}
}
