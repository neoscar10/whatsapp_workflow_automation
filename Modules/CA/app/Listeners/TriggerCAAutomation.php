<?php

namespace Modules\CA\Listeners;

use App\Services\Automations\AutomationTriggerService;
use App\Models\AutomationNode;
use Illuminate\Support\Facades\Log;

class TriggerCAAutomation
{
    public function __construct(
        protected AutomationTriggerService $triggerService
    ) {}

    public function handle(object $event): void
    {
        $payload = $this->buildPayload($event);
        if (!$payload) {
            return; // Not a mapped event
        }

        // Find all trigger nodes matching this event key across the company
        $triggerNodes = AutomationNode::where('type', 'trigger')
            ->where('subtype', 'ca_event')
            ->whereJsonContains('config->trigger_definition_key', $payload['_trigger_key'])
            ->whereHas('flow', function($q) use ($payload) {
                $q->where('company_id', $payload['company_id'])
                  ->where('is_active', true);
            })
            ->get();

        foreach ($triggerNodes as $node) {
            try {
                $this->triggerService->fireTrigger($node, $payload);
            } catch (\Exception $e) {
                Log::error("Failed to fire CA automation for trigger {$payload['_trigger_key']}", [
                    'node_id' => $node->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function buildPayload(object $event): ?array
    {
        $class = get_class($event);
        
        $basePayload = [];
        $triggerKey = '';
        $companyId = null;

        // Map events to trigger keys and extract base models
        if ($class === \Modules\CA\Events\ComplianceDue::class) {
            $triggerKey = 'ca.compliance_due';
            $compliance = $event->deadline->clientCompliance;
            $basePayload = $this->extractComplianceData($compliance);
            $basePayload['days_remaining'] = $event->daysRemaining;
            $basePayload['deadline_date'] = $event->deadline->due_date ? $event->deadline->due_date->format('Y-m-d') : null;
            $companyId = $compliance->client->company_id;
        } 
        elseif ($class === \Modules\CA\Events\ComplianceOverdue::class) {
            $triggerKey = 'ca.compliance_overdue';
            $basePayload = $this->extractComplianceData($event->compliance);
            $companyId = $event->compliance->client->company_id;
        }
        elseif ($class === \Modules\CA\Events\ComplianceCompleted::class) {
            $triggerKey = 'ca.compliance_completed';
            $basePayload = $this->extractComplianceData($event->compliance);
            $companyId = $event->compliance->client->company_id;
        }
        elseif ($class === \Modules\CA\Events\DocumentUploaded::class) {
            $triggerKey = 'ca.document_uploaded';
            $basePayload = $this->extractDocumentData($event->document);
            $companyId = $event->document->requirement->clientCompliance->client->company_id;
        }
        elseif ($class === \Modules\CA\Events\DocumentApproved::class) {
            $triggerKey = 'ca.document_approved';
            $basePayload = $this->extractDocumentData($event->document);
            $companyId = $event->document->requirement->clientCompliance->client->company_id;
        }
        elseif ($class === \Modules\CA\Events\DocumentRejected::class) {
            $triggerKey = 'ca.document_rejected';
            $basePayload = $this->extractDocumentData($event->document);
            $companyId = $event->document->requirement->clientCompliance->client->company_id;
        }
        elseif ($class === \Modules\CA\Events\RequirementCompleted::class) {
            $triggerKey = 'ca.requirement_completed';
            $basePayload = $this->extractRequirementData($event->requirement);
            $companyId = $event->requirement->clientCompliance->client->company_id;
        }
        else {
            return null; // Event not mapped
        }

        $basePayload['_trigger_key'] = $triggerKey;
        $basePayload['company_id'] = $companyId;

        return $basePayload;
    }

    protected function extractComplianceData($compliance): array
    {
        return [
            'client_name' => $compliance->client->name,
            'client_phone' => $compliance->client->phone,
            'business_type' => $compliance->client->businessType->name ?? '',
            'compliance_name' => $compliance->masterCompliance->name ?? '',
            'firm_name' => $compliance->client->company->name ?? '',
        ];
    }

    protected function extractRequirementData($requirement): array
    {
        $data = $this->extractComplianceData($requirement->clientCompliance);
        $data['requirement_name'] = $requirement->name;
        $data['deadline_date'] = $requirement->due_date ? $requirement->due_date->format('Y-m-d') : null;
        return $data;
    }

    protected function extractDocumentData($document): array
    {
        $data = $this->extractRequirementData($document->requirement);
        $data['document_name'] = $document->original_name;
        $data['document_status'] = $document->status;
        return $data;
    }
}
