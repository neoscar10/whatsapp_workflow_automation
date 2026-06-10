<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceDeadline;
use Modules\CA\Models\CAClientComplianceRequirement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecurringComplianceService
{
    /**
     * Attempt to roll over a completed compliance to the next cycle.
     */
    public function rollover(CAClientCompliance $workspace): void
    {
        // 1. Check if the parent compliance is actually recurring
        $compliance = $workspace->compliance;
        if (!$compliance || !$compliance->is_recurring) {
            return;
        }

        // 2. Find the current pending or recently completed deadline for this workspace
        // Usually, the health service fires ComplianceCompleted before the deadline is formally marked complete,
        // but we'll grab the latest one that is not waived.
        $currentDeadline = $workspace->deadlines()
            ->whereIn('status', ['pending', 'overdue', 'completed'])
            ->orderByDesc('due_date')
            ->first();

        if (!$currentDeadline) {
            return;
        }

        // 3. Mark current deadline as completed if it isn't already
        if ($currentDeadline->status !== 'completed') {
            $currentDeadline->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // 4. Calculate the next due date based on the master frequency
        $masterDeadline = $compliance->complianceDeadlines()->first();
        if (!$masterDeadline) {
            Log::warning("Cannot rollover workspace {$workspace->id}: no master deadline found.");
            return;
        }

        $frequency = $masterDeadline->frequency ?? 'monthly';
        $currentDueDate = Carbon::parse($currentDeadline->due_date);
        
        $nextDueDate = match($frequency) {
            'monthly' => $currentDueDate->copy()->addMonthsNoOverflow(1),
            'quarterly' => $currentDueDate->copy()->addMonthsNoOverflow(3),
            'annually' => $currentDueDate->copy()->addYearsNoOverflow(1),
            default => $currentDueDate->copy()->addMonthsNoOverflow(1)
        };

        // 5. Create the new deadline
        CAClientComplianceDeadline::create([
            'ca_client_compliance_id' => $workspace->id,
            'deadline_name' => $currentDeadline->deadline_name,
            'deadline_type' => $currentDeadline->deadline_type ?? 'Standard',
            'due_date' => $nextDueDate->toDateString(),
            'status' => 'pending',
        ]);

        // 6. Reset all requirements in this workspace so the new documents can be uploaded
        CAClientComplianceRequirement::where('ca_client_compliance_id', $workspace->id)
            ->update([
                'status' => 'pending',
                'is_completed' => false,
                'submitted_at' => null,
                'approved_at' => null,
            ]);

        // 7. Recalculate health which will revert the workspace from 'completed' back to 'pending'
        app(ComplianceHealthService::class)->recalculateHealth($workspace);
        
        Log::info("Successfully rolled over recurring compliance workspace {$workspace->id} to new due date: {$nextDueDate->toDateString()}");
    }
}
