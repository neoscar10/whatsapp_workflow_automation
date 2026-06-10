<?php

namespace Modules\CA\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Services\ComplianceHealthService;

class RecalculateComplianceHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CAClientCompliance $compliance
    ) {}

    public function handle(ComplianceHealthService $healthService): void
    {
        $healthService->recalculateHealth($this->compliance);
    }
}
