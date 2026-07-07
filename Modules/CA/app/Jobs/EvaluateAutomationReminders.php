<?php

namespace Modules\CA\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Services\ReminderSchedulerService;

class EvaluateAutomationReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $scheduler = app(ReminderSchedulerService::class);
        $scheduler->evaluateAndScheduleReminders();
    }
}
