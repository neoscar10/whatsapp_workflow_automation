<?php

namespace Modules\CA\Listeners;

use Modules\CA\Events\ComplianceCompleted;
use Modules\CA\Services\RecurringComplianceService;

class RolloverRecurringCompliance
{
    public function __construct(
        protected RecurringComplianceService $rolloverService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ComplianceCompleted $event): void
    {
        $this->rolloverService->rollover($event->compliance);
    }
}
