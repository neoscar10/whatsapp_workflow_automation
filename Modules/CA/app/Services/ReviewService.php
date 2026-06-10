<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CADocument;
use Modules\CA\Models\CAClientComplianceRequirement;
use App\Models\User;
use Exception;

class ReviewService
{
    /**
     * Approve a document and update associated requirement status
     */
    public function approveDocument(CADocument $document, User $actor): void
    {
        if ($document->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized to approve this document.");
        }

        $document->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        if ($document->ca_client_compliance_requirement_id) {
            $requirement = CAClientComplianceRequirement::find($document->ca_client_compliance_requirement_id);
            if ($requirement) {
                $requirement->update([
                    'status' => 'approved',
                    'is_completed' => true,
                    'approved_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reject a document and update associated requirement status
     */
    public function rejectDocument(CADocument $document, User $actor, string $reason = null): void
    {
        if ($document->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized to reject this document.");
        }

        $document->update([
            'status' => 'rejected',
        ]);

        if ($document->ca_client_compliance_requirement_id) {
            $requirement = CAClientComplianceRequirement::find($document->ca_client_compliance_requirement_id);
            if ($requirement) {
                $requirement->update([
                    'status' => 'rejected',
                    'is_completed' => false,
                    'remarks' => $reason,
                ]);
            }
        }
    }
}
