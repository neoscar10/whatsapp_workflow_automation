<?php

namespace Modules\CA\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Services\AutomationExecutionService;

class DispatchAutomationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $activityId
    ) {}

    public function handle(): void
    {
        $executionService = app(AutomationExecutionService::class);
        $executionService->executeReminder($this->activityId);
    }
}
