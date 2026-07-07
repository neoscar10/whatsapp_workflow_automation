<?php

namespace Modules\CA\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClientAutomation;

class ClientAutomationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CAClientAutomation $automation
    ) {}
}
