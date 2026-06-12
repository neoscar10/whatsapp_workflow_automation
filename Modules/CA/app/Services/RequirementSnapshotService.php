<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceRequirement;
use App\Models\User;

class RequirementSnapshotService
{
    /**
     * Snapshot master requirements into the client specific table
     */
    public function createSnapshot(CAClientCompliance $clientCompliance, User $actor): void
    {
        $compliance = $clientCompliance->compliance;

        // Ensure requirements exist on the compliance master
        $masterRequirements = $compliance->requirements()->where('status', 'active')->get();

        foreach ($masterRequirements as $masterReq) {
            CAClientComplianceRequirement::firstOrCreate(
                [
                    'ca_client_compliance_id' => $clientCompliance->id,
                    'ca_compliance_requirement_id' => $masterReq->id,
                ],
                [
                    'name' => $masterReq->name,
                    'requirement_type' => $masterReq->requirement_type,
                    'input_type' => $masterReq->input_type,
                    'is_required' => $masterReq->is_required,
                    'is_recurring' => $masterReq->is_recurring,
                    'status' => 'pending',
                ]
            );
        }
    }
}
