<?php

namespace Modules\CA\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClient;

class AutomationConfigurationCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CAClient $client,
        public readonly array    $automationIds
    ) {}
}
