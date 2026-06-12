<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CADocument;
use Modules\CA\Services\DocumentService;
use Modules\CA\Services\ReviewService;
use Illuminate\Support\Facades\Auth;

class ComplianceWorkspace extends Component
{
    use WithFileUploads;

    public $client;
    public $clientCompliance;
    
    // Upload state tracking
    public $uploads = []; // requirement_id => uploaded_file
    
    // Recurrence config tracking
    public $recurrenceConfigs = []; // requirement_id => ['frequency' => '', 'start_date' => '']
    
    // Modal state
    public $showRecurrenceModal = false;
    public $editingRequirementId = null;

    // Dynamic recurrence configuration
    public $configureFrequency = '';
    public $configureConfig = [];
    public $configureNextDueDatePreview = null;

    public function openRecurrenceModal($requirementId)
    {
        $this->editingRequirementId = $requirementId;
        $req = CAClientComplianceRequirement::findOrFail($requirementId);
        
        $this->configureFrequency = $req->recurrence_frequency ?? '';
        $this->configureConfig = $req->recurrence_config ?? [];
        if (empty($this->configureConfig) && $this->configureFrequency === 'weekly') {
            $this->configureConfig['days'] = [];
        }
        $this->updateNextDueDatePreview();
        $this->showRecurrenceModal = true;
    }

    public function closeRecurrenceModal()
    {
        $this->showRecurrenceModal = false;
        $this->editingRequirementId = null;
        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->configureNextDueDatePreview = null;
        $this->resetErrorBag(['configureFrequency', 'configureConfig.*']);
    }

    public function updatedConfigureFrequency()
    {
        $this->configureConfig = [];
        if ($this->configureFrequency === 'weekly') {
            $this->configureConfig['days'] = [];
        }
        $this->updateNextDueDatePreview();
    }

    public function updatedConfigureConfig()
    {
        $this->updateNextDueDatePreview();
    }

    public function updateNextDueDatePreview()
    {
        if (empty($this->configureFrequency) || empty($this->configureConfig)) {
            $this->configureNextDueDatePreview = null;
            return;
        }
        
        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        $nextDate = $deadlineService->calculateNextDueDate($this->configureFrequency, $this->configureConfig);
        $this->configureNextDueDatePreview = $nextDate ? $nextDate->format('d M Y') : null;
    }

    public function mount($clientId, $clientComplianceId)
    {
        $this->client = CAClient::where('company_id', Auth::user()->company_id)
            ->findOrFail($clientId);
            
        $this->clientCompliance = CAClientCompliance::with([
            'compliance',
            'clientRequirements.complianceRequirement',
            'deadlines',
            'documents',
            'timelines'
        ])
        ->where('ca_client_id', $clientId)
        ->findOrFail($clientComplianceId);

        foreach ($this->clientCompliance->clientRequirements as $req) {
            if ($req->is_recurring) {
                $this->recurrenceConfigs[$req->id] = [
                    'frequency' => $req->recurrence_frequency ?? 'monthly',
                    'start_date' => $req->next_due_date ? \Carbon\Carbon::parse($req->next_due_date)->format('Y-m-d') : now()->addDays(7)->format('Y-m-d'),
                ];
            }
        }
    }

    public function saveRecurrenceConfig()
    {
        $freq = $this->configureFrequency;
        $config = $this->configureConfig;
        $this->resetErrorBag(['configureFrequency', 'configureConfig.*']);
        
        if (empty($freq)) {
            $this->addError('configureFrequency', 'Select a frequency.');
            return;
        }

        if ($freq === 'weekly' && empty($config['days'])) {
            $this->addError('configureConfig.days', 'Select at least one day.');
            return;
        } elseif ($freq === 'monthly' && empty($config['day_of_month'])) {
            $this->addError('configureConfig.day_of_month', 'Select a day of the month.');
            return;
        } elseif ($freq === 'quarterly' && (empty($config['quarter_type']) || !isset($config['due_days_after_quarter_end']))) {
            $this->addError('configureConfig.quarter_type', 'Select quarter type and due days.');
            return;
        } elseif ($freq === 'yearly' && (empty($config['month']) || empty($config['day']))) {
            $this->addError('configureConfig.month', 'Select month and day.');
            return;
        } elseif ($freq === 'custom' && empty($config['interval'])) {
            $this->addError('configureConfig.interval', 'Enter repeat interval.');
            return;
        }

        $requirement = CAClientComplianceRequirement::findOrFail($this->editingRequirementId);
        
        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        $nextDate = $deadlineService->calculateNextDueDate($this->configureFrequency, $this->configureConfig);

        $requirement->update([
            'recurrence_frequency' => $this->configureFrequency,
            'recurrence_config' => $this->configureConfig,
            'next_due_date' => $nextDate ? $nextDate->toDateString() : null,
        ]);

        $deadlineService->generateRecurringDeadlines($requirement);

        session()->flash('message', 'Recurrence configuration saved successfully!');
        $this->clientCompliance->refresh();
        $this->closeRecurrenceModal();
    }

    public function uploadDocument($requirementId)
    {
        $this->validate([
            'uploads.' . $requirementId => 'required|file|max:10240', // 10MB default
        ]);

        $file = $this->uploads[$requirementId];
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);

        $documentService = app(DocumentService::class);
        
        $document = $documentService->storeDocument($file, Auth::user(), [
            'ca_client_id' => $this->client->id,
            'ca_client_compliance_id' => $this->clientCompliance->id,
            'ca_client_compliance_requirement_id' => $requirement->id,
            'document_name' => clone $requirement->name,
        ]);

        $requirement->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        session()->flash('message', 'Document uploaded successfully!');
        
        // Reset the upload input
        unset($this->uploads[$requirementId]);
        
        // Refresh component data
        $this->clientCompliance->refresh();
    }

    public function approveRequirement($requirementId)
    {
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);
        $document = CADocument::where('ca_client_compliance_requirement_id', $requirementId)->latest()->first();
        
        if ($document) {
            $reviewService = app(ReviewService::class);
            $reviewService->approveDocument($document, Auth::user());
        } else {
            // Text or boolean requirement, no doc to approve
            $requirement->update([
                'status' => 'approved',
                'is_completed' => true,
                'approved_at' => now(),
            ]);
        }

        $this->clientCompliance->refresh();
    }

    public function rejectRequirement($requirementId)
    {
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);
        $document = CADocument::where('ca_client_compliance_requirement_id', $requirementId)->latest()->first();
        
        if ($document) {
            $reviewService = app(ReviewService::class);
            $reviewService->rejectDocument($document, Auth::user(), "Rejected by reviewer.");
        } else {
            $requirement->update([
                'status' => 'rejected',
                'is_completed' => false,
            ]);
        }

        $this->clientCompliance->refresh();
    }

    public function render()
    {
        $total = $this->clientCompliance->clientRequirements->count();
        $completed = $this->clientCompliance->clientRequirements->where('is_completed', true)->count();
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

        return view('ca::livewire.compliance-workspace', [
            'progress' => $progress,
            'totalRequirements' => $total,
            'completedRequirements' => $completed
        ])->layout('layouts.panel');
    }
}
