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
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    // Rejection modal state
    public $showRejectModal = false;
    public $rejectingRequirementId = null;
    public $rejectionReason = '';

    // Dynamic recurrence configuration
    public $configureFrequency = '';
    public $configureConfig = [];
    public $configureNextDueDatePreview = null;
    public $configureSchedules = [];

    public function openRecurrenceModal($requirementId)
    {
        $this->editingRequirementId = $requirementId;
        $req = CAClientComplianceRequirement::findOrFail($requirementId);
        
        $this->configureSchedules = [];
        $existingConfig = $req->recurrence_config ?? [];
        $existingFreq = $req->recurrence_frequency ?? '';
        
        if (isset($existingConfig['schedules']) && is_array($existingConfig['schedules'])) {
            $this->configureSchedules = $existingConfig['schedules'];
        } elseif (!empty($existingFreq)) {
            $this->configureSchedules[] = [
                'frequency' => $existingFreq,
                'config' => $existingConfig,
            ];
        }
        
        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->updateNextDueDatePreview();
        $this->showRecurrenceModal = true;
    }

    public function closeRecurrenceModal()
    {
        $this->showRecurrenceModal = false;
        $this->editingRequirementId = null;
        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->configureSchedules = [];
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
        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        $tempSchedules = $this->configureSchedules;
        
        if (!empty($this->configureFrequency)) {
            $valid = false;
            $freq = $this->configureFrequency;
            $config = $this->configureConfig;
            
            if ($freq === 'daily' && !empty($config['time'])) {
                $valid = true;
            } elseif ($freq === 'weekly' && !empty($config['days'])) {
                $valid = true;
            } elseif ($freq === 'monthly' && !empty($config['day_of_month'])) {
                $valid = true;
            } elseif ($freq === 'quarterly' && !empty($config['quarter_type']) && isset($config['due_days_after_quarter_end'])) {
                $valid = true;
            } elseif ($freq === 'yearly' && !empty($config['month']) && !empty($config['day'])) {
                $valid = true;
            }
            
            if ($valid) {
                $tempSchedules[] = [
                    'frequency' => $freq,
                    'config' => $config,
                ];
            }
        }
        
        if (empty($tempSchedules)) {
            $this->configureNextDueDatePreview = null;
            return;
        }
        
        $nextDate = $deadlineService->calculateNextDueDateForRequirement('multiple', ['schedules' => $tempSchedules]);
        $this->configureNextDueDatePreview = $nextDate ? $nextDate->format('d M Y') : null;
    }

    public function mount($clientId, $clientComplianceId)
    {
        $this->client = CAClient::where('company_id', Auth::user()->company_id)
            ->findOrFail($clientId);
            
        $this->clientCompliance = $this->loadCompliance($clientId, $clientComplianceId);

        foreach ($this->clientCompliance->clientRequirements as $req) {
            if ($req->is_recurring) {
                $this->recurrenceConfigs[$req->id] = [
                    'frequency' => $req->recurrence_frequency ?? 'monthly',
                    'start_date' => $req->next_due_date ? \Carbon\Carbon::parse($req->next_due_date)->format('Y-m-d') : now()->addDays(7)->format('Y-m-d'),
                ];
            }
        }
    }

    /**
     * Load the clientCompliance with all necessary relations.
     */
    private function loadCompliance($clientId, $clientComplianceId): CAClientCompliance
    {
        return CAClientCompliance::with([
            'compliance',
            'clientRequirements.complianceRequirement',
            'clientRequirements.automationDocuments.clientAutomation',
            'clientRequirements.documents',
            'deadlines',
            'documents',
            'timelines'
        ])
        ->where('ca_client_id', $clientId)
        ->findOrFail($clientComplianceId);
    }

    /**
     * Re-load all compliance data including eager-loaded relations.
     */
    private function reloadData(): void
    {
        $this->clientCompliance = $this->loadCompliance(
            $this->client->id,
            $this->clientCompliance->id
        );
    }

    public function addSchedule()
    {
        $freq = $this->configureFrequency;
        $config = $this->configureConfig;
        $this->resetErrorBag(['configureFrequency', 'configureConfig.*']);
        
        if (empty($freq)) {
            $this->addError('configureFrequency', 'Select a frequency.');
            return;
        }

        if ($freq === 'daily' && empty($config['time'])) {
            $this->addError('configureConfig.time', 'Select a time of day.');
            return;
        } elseif ($freq === 'weekly' && empty($config['days'])) {
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

        $this->configureSchedules[] = [
            'frequency' => $freq,
            'config' => $config,
        ];

        $this->configureFrequency = '';
        $this->configureConfig = [];
        
        $this->updateNextDueDatePreview();
    }

    public function removeSchedule($index)
    {
        if (isset($this->configureSchedules[$index])) {
            unset($this->configureSchedules[$index]);
            $this->configureSchedules = array_values($this->configureSchedules);
        }
        $this->updateNextDueDatePreview();
    }

    public function saveRecurrenceConfig()
    {
        $this->resetErrorBag(['configureFrequency', 'configureConfig.*']);
        
        if (empty($this->configureSchedules)) {
            if (empty($this->configureFrequency)) {
                $this->addError('configureFrequency', 'Please configure and add at least one schedule.');
                return;
            } else {
                $this->addSchedule();
                if (!empty($this->getErrorBag()->all())) {
                    return;
                }
            }
        }

        $requirement = CAClientComplianceRequirement::findOrFail($this->editingRequirementId);
        
        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        
        $finalFreq = 'multiple';
        if (count($this->configureSchedules) === 1) {
            $finalFreq = $this->configureSchedules[0]['frequency'];
        }
        
        $finalConfig = [
            'schedules' => $this->configureSchedules
        ];
        
        $nextDate = $deadlineService->calculateNextDueDateForRequirement($finalFreq, $finalConfig);

        $requirement->update([
            'recurrence_frequency' => $finalFreq,
            'recurrence_config' => $finalConfig,
            'next_due_date' => $nextDate ? $nextDate->toDateString() : null,
        ]);

        $deadlineService->generateRecurringDeadlines($requirement);

        session()->flash('message', 'Recurrence configuration saved successfully!');
        $this->reloadData();
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
        
        // Refresh component data (re-loads relations)
        $this->reloadData();
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
            if ($requirement->is_recurring) {
                $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
                $from = $requirement->next_due_date ? \Carbon\Carbon::parse($requirement->next_due_date)->addDay() : now()->addDay();
                $nextDate = $deadlineService->calculateNextDueDateForRequirement(
                    $requirement->recurrence_frequency,
                    $requirement->recurrence_config ?? [],
                    $from
                );

                $requirement->update([
                    'status' => 'pending',
                    'is_completed' => false,
                    'next_due_date' => $nextDate ? $nextDate->toDateString() : null,
                    'approved_at' => now(),
                ]);

                $deadlineService->generateRecurringDeadlines($requirement);
            } else {
                $requirement->update([
                    'status' => 'approved',
                    'is_completed' => true,
                    'approved_at' => now(),
                ]);
            }
        }

        $this->reloadData();
    }

    public function openRejectModal($requirementId)
    {
        $this->rejectingRequirementId = $requirementId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->rejectingRequirementId = null;
        $this->rejectionReason = '';
    }

    public function confirmRejectRequirement()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:1000',
        ], [
            'rejectionReason.required' => 'Please provide a reason for rejection.',
            'rejectionReason.min' => 'The rejection reason must be at least 5 characters.',
        ]);

        $requirementId = $this->rejectingRequirementId;
        $requirement = CAClientComplianceRequirement::findOrFail($requirementId);
        $document = CADocument::where('ca_client_compliance_requirement_id', $requirementId)->latest()->first();

        if ($document) {
            $reviewService = app(ReviewService::class);
            $reviewService->rejectDocument($document, Auth::user(), $this->rejectionReason);
        } else {
            $requirement->update([
                'status' => 'rejected',
                'is_completed' => false,
                'remarks' => $this->rejectionReason,
            ]);
        }

        // Send WhatsApp rejection notification to client
        $this->sendRejectionWhatsAppMessage($requirement, $this->rejectionReason);

        $this->closeRejectModal();
        $this->reloadData();
        session()->flash('message', 'Document rejected and client has been notified via WhatsApp.');
    }

    /**
     * Send a WhatsApp message to the client explaining why their document was rejected.
     */
    private function sendRejectionWhatsAppMessage(CAClientComplianceRequirement $requirement, string $reason): void
    {
        try {
            $client = $this->client;
            if (!$client || !$client->phone) {
                return;
            }

            $companyId = Auth::user()->company_id;
            $phone = $client->phone;

            // Find existing conversation with this client
            $conversation = Conversation::where('company_id', $companyId)
                ->where(function ($q) use ($phone) {
                    $q->where('contact_phone', $phone)
                      ->orWhere('contact_phone', 'like', '%' . substr(preg_replace('/[^0-9]/', '', $phone), -10));
                })
                ->first();

            if (!$conversation) {
                Log::info("ComplianceWorkspace: No WhatsApp conversation found for client phone {$phone}, skipping rejection notification.");
                return;
            }

            $docName = $requirement->name;
            $clientName = $client->client_name;
            $complianceName = $this->clientCompliance->compliance->name ?? 'your compliance';

            $messageText = "❌ *Document Rejected*\n\n";
            $messageText .= "Dear {$clientName},\n\n";
            $messageText .= "The document you submitted for *{$docName}* under *{$complianceName}* has been reviewed and unfortunately could not be accepted.\n\n";
            $messageText .= "📋 *Reason for Rejection:*\n{$reason}\n\n";
            $messageText .= "Please review the feedback above and resubmit the correct document at your earliest convenience.\n\n";
            $messageText .= "Thank you for your cooperation.";

            $msg = ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'direction'       => 'outbound',
                'message_type'    => 'text',
                'body'            => $messageText,
                'status'          => 'queued',
            ]);

            $outboundService = app(WhatsAppOutboundMessageService::class);
            $outboundService->sendConversationMessage($msg);
        } catch (\Exception $e) {
            Log::error('ComplianceWorkspace: Failed to send rejection WhatsApp message: ' . $e->getMessage());
        }
    }



    /**
     * Redirect to the client's direct WhatsApp chat inbox (DM).
     */
    public function redirectToDM()
    {
        $client = $this->client;
        if (!$client || !$client->phone) {
            session()->flash('error', 'Client phone number is missing.');
            return;
        }

        $companyId = Auth::user()->company_id;
        $phone = $client->phone;

        // Find existing conversation
        $conversation = Conversation::where('company_id', $companyId)
            ->where(function ($q) use ($phone) {
                $q->where('contact_phone', $phone)
                  ->orWhere('contact_phone', 'like', '%' . substr(preg_replace('/[^0-9]/', '', $phone), -10));
            })
            ->first();

        // Create a new conversation if it doesn't exist
        if (!$conversation) {
            $conversation = Conversation::create([
                'company_id'        => $companyId,
                'contact_phone'     => $phone,
                'contact_name'      => $client->client_name,
                'status'            => 'open',
                'assignment_status' => 'unassigned',
            ]);
        }

        return redirect()->route('chats.index', ['conversation' => $conversation->id]);
    }

    public function render()
    {
        // Compute stats fresh from DB to avoid stale collection values
        $recurringReqs = \Modules\CA\Models\CAClientComplianceRequirement::where(
            'ca_client_compliance_id', $this->clientCompliance->id
        )->where('is_recurring', true)->get();

        $totalRecurring = $recurringReqs->count();
        $approvedCount  = $recurringReqs->where('status', 'approved')->count();
        $inReviewCount  = $recurringReqs->whereIn('status', ['uploaded', 'under_review'])->count();
        $pendingCount   = $recurringReqs->whereIn('status', ['pending', 'rejected'])->count();
        $percentage     = $totalRecurring > 0 ? round(($approvedCount / $totalRecurring) * 100) : 0;

        return view('ca::livewire.compliance-workspace', [
            'progress'            => $percentage,
            'totalRequirements'   => $totalRecurring,
            'completedRequirements' => $approvedCount,
            'sidebarApproved'     => $approvedCount,
            'sidebarInReview'     => $inReviewCount,
            'sidebarPending'      => $pendingCount,
            'sidebarPercentage'   => $percentage,
        ])->layout('layouts.panel');
    }
}
