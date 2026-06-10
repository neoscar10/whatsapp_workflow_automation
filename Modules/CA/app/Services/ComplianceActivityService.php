<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CADocument;
use Modules\CA\Models\CAClientComplianceDeadline;
use App\Models\User;

class ComplianceActivityService
{
    public function __construct(
        protected ComplianceTimelineService $timelineService
    ) {}

    public function recordDocumentUploaded(CADocument $document, User $uploader): void
    {
        $requirement = $document->requirement;
        $compliance = $requirement->clientCompliance;

        $this->timelineService->logEvent(
            companyId: $compliance->client->company_id,
            clientId: $compliance->ca_client_id,
            eventKey: 'document_uploaded',
            title: 'Document Uploaded',
            description: "Document uploaded for requirement: {$requirement->masterRequirement->name}",
            complianceId: $compliance->id,
            requirementId: $requirement->id,
            documentId: $document->id,
            actor: $uploader
        );
    }

    public function recordDocumentApproved(CADocument $document, User $reviewer): void
    {
        $requirement = $document->requirement;
        $compliance = $requirement->clientCompliance;

        $this->timelineService->logEvent(
            companyId: $compliance->client->company_id,
            clientId: $compliance->ca_client_id,
            eventKey: 'document_approved',
            title: 'Document Approved',
            description: "Document approved for requirement: {$requirement->masterRequirement->name}",
            complianceId: $compliance->id,
            requirementId: $requirement->id,
            documentId: $document->id,
            actor: $reviewer
        );
    }

    public function recordDocumentRejected(CADocument $document, User $reviewer, string $reason): void
    {
        $requirement = $document->requirement;
        $compliance = $requirement->clientCompliance;

        $this->timelineService->logEvent(
            companyId: $compliance->client->company_id,
            clientId: $compliance->ca_client_id,
            eventKey: 'document_rejected',
            title: 'Document Rejected',
            description: "Document rejected. Reason: {$reason}",
            complianceId: $compliance->id,
            requirementId: $requirement->id,
            documentId: $document->id,
            actor: $reviewer,
            metadata: ['rejection_reason' => $reason]
        );
    }

    public function recordStatusChanged(CAClientCompliance $compliance, string $oldStatus, string $newStatus): void
    {
        $this->timelineService->logEvent(
            companyId: $compliance->client->company_id,
            clientId: $compliance->ca_client_id,
            eventKey: 'status_changed',
            title: 'Health Status Changed',
            description: "Health status updated from {$oldStatus} to {$newStatus}",
            complianceId: $compliance->id,
            metadata: ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    public function recordReminderSent(CAClientComplianceDeadline $deadline, string $reminderType): void
    {
        $compliance = $deadline->clientCompliance;

        $this->timelineService->logEvent(
            companyId: $compliance->client->company_id,
            clientId: $compliance->ca_client_id,
            eventKey: 'reminder_sent',
            title: 'Reminder Sent',
            description: "Automated {$reminderType} reminder sent to client.",
            complianceId: $compliance->id,
            metadata: ['reminder_type' => $reminderType, 'deadline_id' => $deadline->id]
        );
    }
}
