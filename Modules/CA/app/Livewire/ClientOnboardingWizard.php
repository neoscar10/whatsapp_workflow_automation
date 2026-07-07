<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientAutomationRule;
use Modules\CA\Services\CAClientService;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CACompliance;
use Modules\CA\Services\AutomationSuggestionService;
use Modules\CA\Services\AutomationConfigurationService;
use Modules\CA\Services\AutomationTemplateLibraryService;
use Modules\CA\Services\ReminderRuleService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;

class ClientOnboardingWizard extends Component
{
    use WithFileUploads;

    #[Url(as: 'draft_id')]
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
    public $configureSchedules = [];

    // Step 4/5: Automation Attachments
    public array $automationsEnabledByDoc = [];
    public $configureAutomationId = '';
    public $companyAutomations = [];

    // Reminder Modal
    public bool $showReminderModal = false;
    public $reminderModalAutomationId = null;
    public array $editingRules = [];
    public $editingMessageTitle = '';
    public $editingMessageBody = '';
    public $editingAutomationLibraryId = null;

    // Textarea Detail Modal
    public bool $showTextareaModal = false;
    public $textareaModalDocId = null;
    public string $textareaModalDocName = '';
    public string $textareaModalValue = '';

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
        
        $draftId = $this->draft_client_id ?? request()->query('draft_id');
        
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
                $this->persistRecurrenceSchedules();
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
        if ($step === 5) {
            $this->persistRecurrenceSchedules();
        }
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
        $this->configureSchedules = [];
        
        if (!empty($existing)) {
            $config = $existing['config'] ?? [];
            $frequency = $existing['frequency'] ?? '';
            
            if (isset($config['schedules']) && is_array($config['schedules'])) {
                $this->configureSchedules = $config['schedules'];
            } elseif (!empty($frequency)) {
                $this->configureSchedules[] = [
                    'frequency' => $frequency,
                    'config' => $config,
                    'automation_id' => null,
                ];
            }
        }
        
        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->configureAutomationId = '';
        $this->updateNextDueDatePreview();

        // Load company automations
        $this->companyAutomations = \Modules\CA\Models\CAClientAutomation::where('company_id', Auth::user()->company_id)
            ->whereNull('client_id')
            ->with('automationLibrary')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->automationLibrary->name . ' (' . ucfirst($a->frequency) . ')',
                'frequency' => $a->frequency
            ])->toArray();
    }

    public function closeRecurrenceModal()
    {
        $this->configuringRequirementId = null;
        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->configureSchedules = [];
        $this->configureNextDueDatePreview = null;
        $this->resetErrorBag(['configureFrequency', 'configureConfig.*']);
    }

    public function openTextareaModal($docId, string $docName): void
    {
        $this->textareaModalDocId   = $docId;
        $this->textareaModalDocName = $docName;
        $this->textareaModalValue   = $this->collectedData[$docId] ?? '';
        $this->showTextareaModal    = true;
    }

    public function saveTextareaModal(): void
    {
        if ($this->textareaModalDocId !== null) {
            $this->collectedData[$this->textareaModalDocId] = $this->textareaModalValue;
        }
        $this->closeTextareaModal();
    }

    public function closeTextareaModal(): void
    {
        $this->showTextareaModal    = false;
        $this->textareaModalDocId   = null;
        $this->textareaModalDocName = '';
        $this->textareaModalValue   = '';
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
            'automation_id' => $this->configureAutomationId ?: null,
        ];

        $this->configureFrequency = '';
        $this->configureConfig = [];
        $this->configureAutomationId = '';
        
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

    public function saveRecurrenceModal()
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

        $deadlineService = app(\Modules\CA\Services\DeadlineService::class);
        
        $finalFreq = 'multiple';
        if (count($this->configureSchedules) === 1) {
            $finalFreq = $this->configureSchedules[0]['frequency'];
        }
        
        $finalConfig = [
            'schedules' => $this->configureSchedules
        ];
        
        $nextDate = $deadlineService->calculateNextDueDateForRequirement($finalFreq, $finalConfig);

        $this->recurrenceConfigs[$this->configuringRequirementId] = [
            'frequency' => $finalFreq,
            'config' => $finalConfig,
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

    public function completeOnboardingProcess()
    {
        Log::info('ClientOnboardingWizard::submit called', [
            'draft_client_id' => $this->draft_client_id,
            'collectedData' => $this->collectedData,
        ]);
        $clientService = app(CAClientService::class);
        $actor = Auth::user();

        try {
            if ($this->draft_client_id) {
                $client = CAClient::findOrFail($this->draft_client_id);
                
                // Completing the onboarding
                $clientService->completeOnboarding($actor, $client);
                
                // Save recurrence configurations
                $this->persistRecurrenceSchedules();

                // Clone attached company-level automations to client-level active automations
                $this->cloneAttachedAutomations($client);

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
                return $this->redirect(route('ca.clients.show', $client->id), navigate: true);
            }
        } catch (Exception $e) {
            session()->flash('error', 'Error completing onboarding: ' . $e->getMessage());
        }
    }

    // ─── Automation Step 5 Methods ────────────────────────────────────────────

    public function loadAutomationSuggestions(): void
    {
        if (empty($this->draft_client_id)) return;

        try {
            $clientId = $this->draft_client_id;
            $client = CAClient::findOrFail($clientId);
            $recurringCount = \Modules\CA\Models\CAClientComplianceRequirement::whereHas('clientCompliance', function($q) use ($clientId) {
                $q->where('ca_client_id', $clientId);
            })
            ->where('is_recurring', true)
            ->count();

            if ($recurringCount === 0) {
                $this->generateMissingRecurringRequirements($client);
            }

            $suggestionService = app(AutomationSuggestionService::class);
            $templateService   = app(AutomationTemplateLibraryService::class);

            $raw = $suggestionService->suggestForClient($this->draft_client_id);

            $this->automationSuggestions = [];
            $this->automationAiError = null;

            foreach ($raw as $suggestion) {
                $library   = $suggestion['library'];
                $frequency = $suggestion['frequency'];
                $libraryId = $library->id;

                // Get or generate AI template (DB cache first)
                $template = $templateService->getOrGenerateTemplate($library);

                // Pre-populate config if not already set
                if (!isset($this->automationConfigs[$libraryId])) {
                    $defaultRules = app(ReminderRuleService::class)->getDefaultRules($frequency);
                    $this->automationConfigs[$libraryId] = [
                        'library_id'           => $libraryId,
                        'frequency'            => $frequency,
                        'is_enabled'           => true,
                        'requirement_ids'      => $suggestion['documents']->pluck('id')->toArray(),
                        'rules'                => $defaultRules,
                        'custom_message_title' => $template['message_title'],
                        'custom_message_body'  => $template['message_body'],
                    ];
                }

                $this->automationSuggestions[] = [
                    'library_id'               => $libraryId,
                    'name'                     => $library->name,
                    'frequency'                => $frequency,
                    'icon'                     => $library->icon,
                    'color'                    => $library->color,
                    'documents'                => $suggestion['documents']->map(fn($d) => [
                        'id'   => $d->id,
                        'name' => $d->name,
                    ])->toArray(),
                    'estimated_reminder_count' => $suggestion['estimated_reminder_count'],
                    'message_title'            => $template['message_title'],
                    'message_body'             => $template['message_body'],
                ];
            }
        } catch (Exception $e) {
            Log::error('loadAutomationSuggestions failed: ' . $e->getMessage());
            $this->automationAiError = 'Could not load automation suggestions. You can configure them later.';
        }
    }

    public function openReminderModal(int $libraryId): void
    {
        $this->editingAutomationLibraryId = $libraryId;
        $config = $this->automationConfigs[$libraryId] ?? [];
        $this->editingRules         = $config['rules'] ?? [];
        $this->editingMessageTitle  = $config['custom_message_title'] ?? '';
        $this->editingMessageBody   = $config['custom_message_body'] ?? '';
        $this->showReminderModal    = true;
    }

    public function closeReminderModal(): void
    {
        $this->showReminderModal          = false;
        $this->editingAutomationLibraryId = null;
        $this->editingRules               = [];
        $this->editingMessageTitle        = '';
        $this->editingMessageBody         = '';
    }

    public function addReminderRule(): void
    {
        $this->editingRules[] = [
            'trigger_type' => 'before_due',
            'offset_days'  => 1,
            'send_time'    => '09:00',
            'is_enabled'   => true,
        ];
    }

    public function removeReminderRule(int $index): void
    {
        if (isset($this->editingRules[$index])) {
            unset($this->editingRules[$index]);
            $this->editingRules = array_values($this->editingRules);
        }
    }

    public function saveReminderModal(): void
    {
        $libraryId = $this->editingAutomationLibraryId;
        if (!$libraryId) {
            $this->closeReminderModal();
            return;
        }

        try {
            app(ReminderRuleService::class)->validate($this->editingRules);
        } catch (Exception $e) {
            $this->addError('editingRules', $e->getMessage());
            return;
        }

        // Persist the edited config back into the automationConfigs state array
        if (isset($this->automationConfigs[$libraryId])) {
            $this->automationConfigs[$libraryId]['rules']                = $this->editingRules;
            $this->automationConfigs[$libraryId]['custom_message_title'] = $this->editingMessageTitle;
            $this->automationConfigs[$libraryId]['custom_message_body']  = $this->editingMessageBody;
        }

        $this->closeReminderModal();
    }

    public function toggleAutomation(int $libraryId): void
    {
        if (isset($this->automationConfigs[$libraryId])) {
            $this->automationConfigs[$libraryId]['is_enabled'] =
                !$this->automationConfigs[$libraryId]['is_enabled'];
        }
    }

    public function resetMessageToDefault(int $libraryId): void
    {
        $suggestion = collect($this->automationSuggestions)
            ->firstWhere('library_id', $libraryId);

        if ($suggestion && isset($this->automationConfigs[$libraryId])) {
            $this->automationConfigs[$libraryId]['custom_message_title'] = $suggestion['message_title'];
            $this->automationConfigs[$libraryId]['custom_message_body']  = $suggestion['message_body'];
            // Also sync editing fields if modal is open
            $this->editingMessageTitle = $suggestion['message_title'];
            $this->editingMessageBody  = $suggestion['message_body'];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('ca::livewire.client-onboarding-wizard')->layout('layouts.panel');
    }

    private function persistRecurrenceSchedules(): void
    {
        if (empty($this->draft_client_id)) return;
        
        $client = CAClient::findOrFail($this->draft_client_id);
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
    }

    private function cloneAttachedAutomations(CAClient $client): void
    {
        $actor = Auth::user();
        
        // Delete existing client automations to avoid duplicates
        $existingAutomations = CAClientAutomation::where('client_id', $client->id)->get();
        foreach ($existingAutomations as $ea) {
            $ea->rules()->delete();
            $ea->documentMappings()->delete();
            $ea->delete();
        }

        // Load all client compliance requirements
        $clientRequirements = \Modules\CA\Models\CAClientComplianceRequirement::whereHas('clientCompliance', function ($q) use ($client) {
            $q->where('ca_client_id', $client->id);
        })->get();

        foreach ($this->recurrenceConfigs as $reqId => $data) {
            // Check if automation is enabled for this document (default is true)
            if (!($this->automationsEnabledByDoc[$reqId] ?? true)) {
                continue;
            }

            $schedules = $data['config']['schedules'] ?? [];
            foreach ($schedules as $sched) {
                $automationId = $sched['automation_id'] ?? null;
                if (!$automationId) continue;

                // Find company template automation
                $companyAutomation = CAClientAutomation::where('company_id', $client->company_id)
                    ->whereNull('client_id')
                    ->find($automationId);

                if (!$companyAutomation) continue;

                // Link requirement ID
                $req = $clientRequirements->firstWhere('ca_compliance_requirement_id', $reqId);
                if (!$req) continue;

                // Create client-specific automation
                $clientAutomation = CAClientAutomation::create([
                    'company_id'            => $client->company_id,
                    'client_id'             => $client->id,
                    'automation_library_id' => $companyAutomation->automation_library_id,
                    'whatsapp_template_id'  => $companyAutomation->whatsapp_template_id,
                    'frequency'             => $companyAutomation->frequency,
                    'status'                => 'active',
                    'is_enabled'            => true,
                    'created_by'            => $actor->id,
                    'metadata_json'         => $companyAutomation->metadata_json,
                ]);

                // Copy rules
                foreach ($companyAutomation->rules as $rule) {
                    CAClientAutomationRule::create([
                        'client_automation_id' => $clientAutomation->id,
                        'trigger_type'         => $rule->trigger_type,
                        'offset_days'          => $rule->offset_days,
                        'send_time'            => $rule->send_time,
                        'is_enabled'           => $rule->is_enabled,
                    ]);
                }

                // Create document mapping link
                \Modules\CA\Models\CAClientAutomationDocument::create([
                    'client_automation_id'               => $clientAutomation->id,
                    'ca_client_compliance_requirement_id' => $req->id,
                ]);
            }
        }
    }

    private function generateMissingRecurringRequirements(CAClient $client): void
    {
        $selectedCompliances = $client->clientCompliances()->with('compliance')->get();
        if ($selectedCompliances->isEmpty()) {
            return;
        }

        $generated = false;
        try {
            $aiManager = app(\Modules\CA\Services\AI\Managers\AIManager::class);
            $provider = $aiManager->provider();
            $systemPrompt = "You are an expert Indian Chartered Accountant.";
            $complianceNames = $selectedCompliances->pluck('compliance.name')->implode(', ');
            $userPrompt = "Identify and list the recurring document/filing compliance requirements that are periodically due (e.g. monthly, quarterly, yearly) for these Indian compliances: {$complianceNames}.
            Return the response STRICTLY as a JSON object with the following structure:
            {
                \"requirements\": [
                    {
                        \"compliance_slug\": \"slug-of-the-compliance\",
                        \"name\": \"GSTR-1 Return data\",
                        \"description\": \"Monthly sales data for GSTR-1 filing\",
                        \"requirement_type\": \"document\",
                        \"input_type\": \"file\",
                        \"is_required\": true,
                        \"is_recurring\": true,
                        \"required_stage\": \"post_onboarding\"
                    }
                ]
            }
            Ensure each requirement's compliance_slug matches one of these slugs: " . $selectedCompliances->pluck('compliance.slug')->implode(', ') . ".
            Do not include any markdown formatting, only raw valid JSON.";

            $json = $provider->generateStructuredResponse($systemPrompt, $userPrompt);

            if (isset($json['requirements']) && is_array($json['requirements'])) {
                foreach ($json['requirements'] as $reqData) {
                    $slug = $reqData['compliance_slug'] ?? '';
                    $compliance = \Modules\CA\Models\CACompliance::where('slug', $slug)->first();
                    if (!$compliance) {
                        $compliance = $selectedCompliances->first()->compliance;
                    }

                    if ($compliance) {
                        $masterReq = \Modules\CA\Models\CAComplianceRequirement::updateOrCreate(
                            [
                                'ca_compliance_id' => $compliance->id,
                                'slug' => \Illuminate\Support\Str::slug($reqData['name']),
                            ],
                            [
                                'name' => $reqData['name'],
                                'description' => $reqData['description'] ?? null,
                                'requirement_type' => $reqData['requirement_type'] ?? 'document',
                                'input_type' => $reqData['input_type'] ?? 'file',
                                'is_required' => $reqData['is_required'] ?? true,
                                'is_recurring' => true,
                                'required_stage' => 'post_onboarding',
                            ]
                        );

                        $clientComp = $selectedCompliances->where('ca_compliance_id', $compliance->id)->first();
                        if ($clientComp) {
                            \Modules\CA\Models\CAClientComplianceRequirement::firstOrCreate(
                                [
                                    'ca_client_compliance_id' => $clientComp->id,
                                    'ca_compliance_requirement_id' => $masterReq->id,
                                ],
                                [
                                    'name' => $masterReq->name,
                                    'requirement_type' => $masterReq->requirement_type,
                                    'input_type' => $masterReq->input_type,
                                    'is_required' => $masterReq->is_required,
                                    'is_recurring' => true,
                                    'status' => 'pending',
                                ]
                            );
                            $generated = true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("AI generation failed for recurring requirements: " . $e->getMessage() . ". Falling back to local dictionary.");
        }

        if (!$generated) {
            foreach ($selectedCompliances as $sc) {
                $comp = $sc->compliance;
                $name = $comp->name;

                $fallbackReqs = [];
                if (stripos($name, 'gst') !== false) {
                    $fallbackReqs[] = [
                        'name' => 'GST GSTR-1 Sales data',
                        'desc' => 'Monthly/Quarterly sales register for GST GSTR-1 return filing.',
                    ];
                    $fallbackReqs[] = [
                        'name' => 'GST GSTR-3B purchase register',
                        'desc' => 'Monthly purchase register/ITC ledger for GSTR-3B filing.',
                    ];
                } elseif (stripos($name, 'tds') !== false || stripos($name, 'tax deduction') !== false) {
                    $fallbackReqs[] = [
                        'name' => 'TDS quarterly statement details',
                        'desc' => 'Challans and salary/non-salary deduction statements for quarterly TDS return.',
                    ];
                } elseif (stripos($name, 'income tax') !== false || stripos($name, 'itr') !== false) {
                    $fallbackReqs[] = [
                        'name' => 'Income tax annual ledger',
                        'desc' => 'Audited balance sheet, profit & loss, and form 26AS for annual ITR filing.',
                    ];
                } else {
                    $fallbackReqs[] = [
                        'name' => $name . ' Monthly Report',
                        'desc' => 'Required monthly documents for ' . $name,
                    ];
                }

                foreach ($fallbackReqs as $f) {
                    $masterReq = \Modules\CA\Models\CAComplianceRequirement::updateOrCreate(
                        [
                            'ca_compliance_id' => $comp->id,
                            'slug' => \Illuminate\Support\Str::slug($f['name']),
                        ],
                        [
                            'name' => $f['name'],
                            'description' => $f['desc'],
                            'requirement_type' => 'document',
                            'input_type' => 'file',
                            'is_required' => true,
                            'is_recurring' => true,
                            'required_stage' => 'post_onboarding',
                        ]
                    );

                    \Modules\CA\Models\CAClientComplianceRequirement::firstOrCreate(
                        [
                            'ca_client_compliance_id' => $sc->id,
                            'ca_compliance_requirement_id' => $masterReq->id,
                        ],
                        [
                            'name' => $masterReq->name,
                            'requirement_type' => $masterReq->requirement_type,
                            'input_type' => $masterReq->input_type,
                            'is_required' => $masterReq->is_required,
                            'is_recurring' => true,
                            'status' => 'pending',
                        ]
                    );
                }
            }
        }
    }
}
