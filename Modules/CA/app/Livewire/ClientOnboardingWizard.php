<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CAClient;
use Modules\CA\Services\CAClientService;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CACompliance;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class ClientOnboardingWizard extends Component
{
    use WithFileUploads;

    public $draft_client_id = null;
    public $step = 1;

    // Step 1: Client Info
    public $client_name;
    public $email;
    public $country_code = '+91';
    public $phone;
    public $address;
    public $city;
    public $state;
    public $country;
    public $notes;

    // Step 2: Business Type
    public $business_type_id;
    public $businessTypes = [];

    // Step 3 & 4: Compliances
    public $isIntelligenceLoaded = false;
    public $isLoadingIntelligence = false;
    public $aiSuggestedCompliances = [];
    public $groupedCompliances = [];
    public $selectedCompliances = [];
    public $ai_error = null;
    public $collectedData = [];
    public $recurrenceConfigs = [];
    public $configuringRequirementId = null;
    public $configureFrequency = '';
    public $configureConfig = [];
    public $configureNextDueDatePreview = null;

    public function rules()
    {
        if ($this->step === 1) {
            return [
                'client_name' => 'required|string|max:255',
                'phone' => 'required|numeric|digits_between:7,15',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'country' => 'nullable|string',
            ];
        }

        if ($this->step === 2) {
            return [
                'business_type_id' => 'required|exists:ca_business_types,id',
            ];
        }

        if ($this->step === 3) {
            return [
                'selectedCompliances' => 'array',
            ];
        }

        return [];
    }

    public function mount()
    {
        $this->businessTypes = CABusinessType::where('status', 'active')->get();
        $actor = Auth::user();
        
        $draftId = request()->query('draft_id');
        
        if ($draftId) {
            $draft = CAClient::where('company_id', $actor->company_id)
                ->where('created_by', $actor->id)
                ->where('status', 'draft')
                ->where('id', $draftId)
                ->first();

            if ($draft) {
                $this->draft_client_id = $draft->id;
                $this->step = $draft->current_step;
                $this->client_name = $draft->client_name;
                $this->email = $draft->email;
                
                // Extract country code naive logic or just map raw
                if ($draft->phone && str_starts_with($draft->phone, '+')) {
                    $this->phone = substr($draft->phone, 3);
                    $this->country_code = substr($draft->phone, 0, 3);
                } else {
                    $this->phone = $draft->phone;
                }
                
                $this->address = $draft->address;
                $this->city = $draft->city;
                $this->state = $draft->state;
                $this->country = $draft->country;
                $this->notes = $draft->notes;
                $this->business_type_id = $draft->ca_business_type_id;

                if ($this->business_type_id && $this->step >= 3) {
                    $this->loadGroupedCompliances();
                    $this->isIntelligenceLoaded = true;
                    
                    if ($this->step >= 4) {
                        $this->selectedCompliances = $draft->clientCompliances()->pluck('ca_compliance_id')->toArray();
                    }
                }
            }
        }
    }

    public function updatedBusinessTypeId()
    {
        $this->isIntelligenceLoaded = false;
    }

    public function nextStep()
    {
        $rules = $this->rules();
        if (!empty($rules)) {
            $this->validate($rules);
        }

        $clientService = app(CAClientService::class);
        $actor = Auth::user();

        // Save Draft Step 1
        if ($this->step === 1) {
            $data = [
                'client_name' => $this->client_name,
                'phone' => $this->country_code . $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'notes' => $this->notes,
            ];

            try {
                if ($this->draft_client_id) {
                    $client = CAClient::findOrFail($this->draft_client_id);
                    $data['current_step'] = 2;
                    $clientService->updateClient($actor, $client, $data);
                } else {
                    $client = $clientService->createClient($actor, $data, null);
                    $this->draft_client_id = $client->id;
                    $client->update(['current_step' => 2]);
                }
            } catch (Exception $e) {
                $this->addError('phone', $e->getMessage());
                return;
            }
        }

        // Save Draft Step 2
        if ($this->step === 2) {
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                $clientService->updateClient($actor, $client, [
                    'ca_business_type_id' => $this->business_type_id,
                    'current_step' => 3
                ]);
            }
        }

        if ($this->step === 3) {
            if (empty($this->selectedCompliances)) {
                $this->addError('selectedCompliances', 'Please select at least one compliance requirement.');
                return;
            }
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                $clientService->updateClient($actor, $client, ['current_step' => 4]);
                // Note: assignments are persisted finally or here as draft assignments.
                // For now, we will assign them at step 3, but the actual submit at 5 wraps it up.
                // It's safer to persist them right before step 4 so requirements show up.
                $clientService->assignCompliances($actor, $client, $this->selectedCompliances);
            }
        }

        if ($this->step === 4) {
            // Validate collectedData against 'Required Now' expected documents
            $requiredDocs = $this->expectedDocuments['Required Now'] ?? collect();
            foreach ($requiredDocs as $doc) {
                if (!isset($this->collectedData[$doc->id]) || empty($this->collectedData[$doc->id])) {
                    $this->addError('collectedData.'.$doc->id, 'This document/input is required.');
                    return;
                }
            }
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                $clientService->updateClient($actor, $client, ['current_step' => 5]);
            }
        }

        if ($this->step === 5) {
            $this->submit();
            return;
        }

        $this->step++;
        $this->js('document.querySelector("main").scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                $client->update(['current_step' => $this->step]);
            }
            $this->js('document.querySelector("main").scrollTo({ top: 0, behavior: "smooth" })');
        }
    }

    public function setStep($step)
    {
        $this->step = $step;
        if ($this->draft_client_id) {
            $client = CAClient::findOrFail($this->draft_client_id);
            $client->update(['current_step' => $this->step]);
        }
        $this->js('document.querySelector("main").scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function loadIntelligence()
    {
        // Removed artificial sleep(2)
        $this->ai_error = null;
        $businessType = CABusinessType::find($this->business_type_id);
        
        try {
            $aiService = app(\Modules\CA\Services\AI\KnowledgeEngineService::class);
            $persistenceService = app(\Modules\CA\Services\ComplianceKnowledgePersistenceService::class);

            $intelligence = $aiService->generateComplianceKnowledge($businessType->name);
            $this->aiSuggestedCompliances = $intelligence ?? [];
            
            if (!empty($intelligence)) {
                $totalAiItems = $persistenceService->persistKnowledge($businessType, $intelligence);
                session()->flash('ai_success', "AI successfully processed compliance requirements.");
            }
        } catch (Exception $e) {
            Log::error("Failed to load AI intelligence: " . $e->getMessage());
            $this->ai_error = "AI Intelligence failed to load. Falling back to saved mapped compliances.";
        }

        $this->loadGroupedCompliances();
        
        // Ensure user must manually select what applies
        if (empty($this->selectedCompliances)) {
             $this->selectedCompliances = [];
        }
        
        $this->isIntelligenceLoaded = true;
    }

    private function loadGroupedCompliances()
    {
        $businessType = CABusinessType::find($this->business_type_id);
        if (!$businessType) return;

        $this->groupedCompliances = CAServiceCategory::with(['compliances' => function($q) use ($businessType) {
            $q->whereHas('businessTypes', function($sq) use ($businessType) {
                $sq->where('ca_business_types.id', $businessType->id);
            })->where('status', 'active');
        }])->whereHas('compliances', function($q) use ($businessType) {
            $q->whereHas('businessTypes', function($sq) use ($businessType) {
                $sq->where('ca_business_types.id', $businessType->id);
            })->where('status', 'active');
        })->orderBy('sort_order')->get();
    }

    public function openRecurrenceModal($reqId)
    {
        $this->configuringRequirementId = $reqId;
        $existing = $this->recurrenceConfigs[$reqId] ?? [];
        $this->configureFrequency = $existing['frequency'] ?? '';
        $this->configureConfig = $existing['config'] ?? [];
        
        if ($this->configureFrequency === 'weekly' && !isset($this->configureConfig['days'])) {
            $this->configureConfig['days'] = [];
        }
        
        $this->updateNextDueDatePreview();
    }

    public function closeRecurrenceModal()
    {
        $this->configuringRequirementId = null;
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

    public function saveRecurrenceModal()
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

        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        $nextDate = $deadlineService->calculateNextDueDate($this->configureFrequency, $this->configureConfig);

        $this->recurrenceConfigs[$this->configuringRequirementId] = [
            'frequency' => $this->configureFrequency,
            'config' => $this->configureConfig,
            'next_due_date' => $nextDate ? $nextDate->toDateString() : null,
        ];
        
        $this->closeRecurrenceModal();
    }

    public function getExpectedDocumentsProperty()
    {
        if (empty($this->selectedCompliances)) {
            return collect();
        }

        return \Modules\CA\Models\CAComplianceRequirement::with('compliance')
            ->whereIn('ca_compliance_id', $this->selectedCompliances)
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                $first = $items->first();
                $first->compliance_names = $items->pluck('compliance.name')->unique()->filter()->implode(', ');
                return $first;
            })
            ->values()
            ->groupBy(function($item) {
                if ($item->is_recurring) {
                    return 'Recurring Tracking';
                }
                return $item->is_required ? 'Required Now' : 'Required Later';
            });
    }

    public function submit()
    {
        $clientService = app(CAClientService::class);
        $actor = Auth::user();

        try {
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                
                // Completing the onboarding
                $clientService->completeOnboarding($actor, $client);
                
                // Save recurrence configurations
                if (!empty($this->recurrenceConfigs)) {
                    $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
                    foreach ($this->recurrenceConfigs as $reqId => $data) {
                        if (!empty($data['frequency']) && !empty($data['next_due_date'])) {
                            $requirements = \Modules\CA\Models\CAClientComplianceRequirement::whereHas('clientCompliance', function ($q) use ($client) {
                                $q->where('ca_client_id', $client->id);
                            })->where('ca_compliance_requirement_id', $reqId)->get();

                            foreach ($requirements as $requirement) {
                                if ($requirement->is_recurring) {
                                    $requirement->update([
                                        'recurrence_frequency' => $data['frequency'],
                                        'recurrence_config' => $data['config'] ?? null,
                                        'next_due_date' => $data['next_due_date'],
                                    ]);
                                    $deadlineService->generateRecurringDeadlines($requirement);
                                }
                            }
                        }
                    }
                }

                $documentService = app(\Modules\CA\Services\DocumentService::class);
                $timelineService = app(\Modules\CA\Services\ComplianceTimelineService::class);
                
                $clientRequirements = \Modules\CA\Models\CAClientComplianceRequirement::whereHas('clientCompliance', function ($q) use ($client) {
                    $q->where('ca_client_id', $client->id);
                })->get();

                foreach ($this->collectedData as $masterReqId => $value) {
                    if (empty($value)) continue;

                    $reqs = $clientRequirements->where('ca_compliance_requirement_id', $masterReqId);
                    foreach($reqs as $req) {
                        if ($value instanceof \Illuminate\Http\UploadedFile) {
                            $doc = $documentService->storeDocument($value, $actor, [
                                'ca_client_id' => $client->id,
                                'ca_client_compliance_id' => $req->ca_client_compliance_id,
                                'ca_client_compliance_requirement_id' => $req->id,
                                'document_name' => $req->name,
                            ]);
                            $req->update([
                                'status' => 'uploaded',
                                'submitted_at' => now(),
                            ]);
                            $timelineService->logEvent(
                                $actor->company_id,
                                $client->id,
                                'document_uploaded',
                                'Document Uploaded',
                                "Uploaded file for {$req->name}",
                                $req->ca_client_compliance_id,
                                $req->id,
                                $doc->id,
                                $actor
                            );
                        } else {
                            $req->update([
                                'status' => 'uploaded',
                                'submitted_at' => now(),
                                'metadata_json' => array_merge($req->metadata_json ?? [], ['collected_data' => $value])
                            ]);
                            $timelineService->logEvent(
                                $actor->company_id,
                                $client->id,
                                'data_submitted',
                                'Data Submitted',
                                "Provided details for {$req->name}",
                                $req->ca_client_compliance_id,
                                $req->id,
                                null,
                                $actor
                            );
                        }
                    }
                }

                session()->flash('message', 'Client successfully onboarded!');
                return redirect()->route('ca.clients.show', $client->id);
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error completing onboarding: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('ca::livewire.client-onboarding-wizard')->layout('layouts.panel');
    }
}
