<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceDeadline;
use Carbon\Carbon;

class DeadlineService
{
    /**
     * Calculate and snapshot deadlines for a client compliance
     */
    public function generateDeadlines(CAClientCompliance $clientCompliance): void
    {
        $compliance = $clientCompliance->compliance;
        $deadlines = $compliance->complianceDeadlines; // the master deadlines

        foreach ($deadlines as $masterDeadline) {
            
            $dueDate = Carbon::now()->addDays(30)->toDateString();
            $deadlineName = $masterDeadline->description ?? ($compliance->name . ' Due');

            CAClientComplianceDeadline::firstOrCreate(
                [
                    'ca_client_compliance_id' => $clientCompliance->id,
                    'deadline_name' => $deadlineName,
                ],
                [
                    'deadline_type' => 'Standard',
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]
            );
        }
    }
}
