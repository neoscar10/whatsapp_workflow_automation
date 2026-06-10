<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAComplianceTimeline;
use Modules\CA\Models\CAClientCompliance;
use App\Models\User;

class ComplianceTimelineService
{
    /**
     * Log an event in the timeline.
     */
    public function logEvent(
        int $companyId,
        int $clientId,
        string $eventKey,
        string $title,
        ?string $description = null,
        ?int $complianceId = null,
        ?int $requirementId = null,
        ?int $documentId = null,
        ?User $actor = null,
        array $metadata = []
    ): CAComplianceTimeline {
        return CAComplianceTimeline::create([
            'company_id' => $companyId,
            'ca_client_id' => $clientId,
            'ca_client_compliance_id' => $complianceId,
            'ca_client_compliance_requirement_id' => $requirementId,
            'ca_document_id' => $documentId,
            'event_key' => $eventKey,
            'title' => $title,
            'description' => $description,
            'actor_id' => $actor ? $actor->id : null,
            'metadata' => $metadata,
        ]);
    }
}
