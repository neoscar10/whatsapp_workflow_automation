<?php

namespace Modules\CA\Console\Commands;

use Illuminate\Console\Command;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Services\RecurringComplianceService;

class RolloverCompliancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ca:rollover-compliances';

    /**
     * The console command description.
     */
    protected $description = 'Sweeps the database to roll over completed recurring compliances to the next cycle.';

    /**
     * Execute the console command.
     */
    public function handle(RecurringComplianceService $rolloverService)
    {
        $this->info('Starting recurring compliance rollover sweep...');

        // Find all CAClientCompliances that are:
        // 1. Marked as health_status = 'completed'
        // 2. The master compliance is_recurring = true
        $completedWorkspaces = CAClientCompliance::where('health_status', 'completed')
            ->whereHas('compliance', function($q) {
                $q->where('is_recurring', true);
            })
            ->get();

        $count = 0;

        foreach ($completedWorkspaces as $workspace) {
            // Because the health_status is "completed", it means all current requirements are done.
            // The RolloverService will generate the next deadline and reset the requirements.
            // If the listener caught it, health_status would already be 'pending' again!
            // Thus, anything we catch here is a genuine missed rollover.
            $rolloverService->rollover($workspace);
            $count++;
        }

        $this->info("Rolled over {$count} recurring compliances.");
    }
}
