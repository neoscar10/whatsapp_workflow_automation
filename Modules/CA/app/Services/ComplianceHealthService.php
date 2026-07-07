<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;

class ComplianceHealthService
{
    public function __construct(
        protected ComplianceActivityService $activityService
    ) {}

    /**
     * Recalculates and updates the health status of a client compliance.
     */
    public function recalculateHealth(CAClientCompliance $compliance): void
    {
        $oldStatus = $compliance->health_status;
        $newStatus = $this->determineStatus($compliance);

        if ($oldStatus !== $newStatus) {
            $compliance->update(['health_status' => $newStatus]);
            
            // Log the status change
            $this->activityService->recordStatusChanged($compliance, $oldStatus, $newStatus);

            // Dispatch event for Automation Trigger (e.g. ca.compliance_completed)
            if ($newStatus === 'completed') {
                event(new \Modules\CA\Events\ComplianceCompleted($compliance));
            } elseif ($newStatus === 'overdue') {
                event(new \Modules\CA\Events\ComplianceOverdue($compliance));
            }
        }
    }

    protected function determineStatus(CAClientCompliance $compliance): string
    {
        // Get all requirements for this compliance snapshot
        $requirements = $compliance->clientRequirements;

        if ($requirements->isEmpty()) {
            return 'pending';
        }

        $allCompleted = true;
        $hasRejected = false;
        $hasOverdue = false;
        $hasAtRisk = false;
        $hasInProgress = false;

        $today = now()->startOfDay();
        $riskThreshold = now()->addDays(7)->endOfDay();

        foreach ($requirements as $req) {
            $isRequired = $req->is_required;
            $isCompleted = $req->is_completed;

            if ($isRequired && !$isCompleted) {
                $allCompleted = false;
            }

            if ($req->status === 'rejected') {
                $hasRejected = true;
            }

            if ($req->status === 'submitted' || $req->status === 'in_progress') {
                $hasInProgress = true;
            }

            if (!$isCompleted && $req->due_date) {
                $dueDate = \Carbon\Carbon::parse($req->due_date)->endOfDay();
                
                if ($dueDate->isPast()) {
                    $hasOverdue = true;
                } elseif ($dueDate->isBetween($today, $riskThreshold)) {
                    $hasAtRisk = true;
                }
            }
        }

        // 1. Blocked
        if ($hasRejected) {
            return 'blocked';
        }

        // 2. Overdue
        if ($hasOverdue) {
            return 'overdue';
        }

        // 3. Completed
        if ($allCompleted) {
            return 'completed';
        }

        // 4. At Risk
        if ($hasAtRisk) {
            return 'at_risk';
        }

        // 5. In Progress
        if ($hasInProgress) {
            return 'in_progress';
        }

        // 6. Pending
        return 'pending';
    }
}
