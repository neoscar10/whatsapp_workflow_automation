<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAutomationNode implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\AutomationRun $run,
        public int $nodeId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(\App\Services\Automations\AutomationRunnerService $runner): void
    {
        $runner->processNodeStep($this->run, $this->nodeId);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        app(\App\Services\Automations\AutomationRunnerService::class)->handleJobFailure($this->run, $exception);
    }
}
