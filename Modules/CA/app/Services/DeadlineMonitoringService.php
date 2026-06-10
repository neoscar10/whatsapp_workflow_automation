<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientComplianceDeadline;
use Modules\CA\Models\CAClientCompliance;
use App\Models\AutomationRun;

class DeadlineMonitoringService
{
    public function __construct(
        protected ComplianceHealthService $healthService
    ) {}

    /**
     * Scan all unmet deadlines and trigger warnings if they fall within thresholds.
     */
    public function monitorDeadlines(): void
    {
        $warningThresholds = [90, 60, 30, 15, 7, 3, 1]; // days

        $deadlines = CAClientComplianceDeadline::whereNull('completed_at')
            ->whereNotNull('due_date')
            ->get();

        $today = now()->startOfDay();

        foreach ($deadlines as $deadline) {
            $dueDate = \Carbon\Carbon::parse($deadline->due_date)->startOfDay();
            
            if ($dueDate->isPast()) {
                // If it just passed, we might want to flag it or rely on health service
                $this->healthService->recalculateHealth($deadline->clientCompliance);
                continue;
            }

            $daysRemaining = $today->diffInDays($dueDate, false);

            if (in_array((int)$daysRemaining, $warningThresholds, true)) {
                $this->triggerDeadlineWarning($deadline, (int)$daysRemaining);
            }
        }
    }

    protected function triggerDeadlineWarning(CAClientComplianceDeadline $deadline, int $daysRemaining): void
    {
        // Recalculate health first (might shift to 'at_risk')
        $this->healthService->recalculateHealth($deadline->clientCompliance);

        // Fire event -> triggering ca.compliance_due automation
        event(new \Modules\CA\Events\ComplianceDue($deadline, $daysRemaining));
    }
}
