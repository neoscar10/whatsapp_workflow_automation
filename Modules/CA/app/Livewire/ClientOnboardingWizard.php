<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CABusinessType;
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

    // Step 3 & 4: Compliances (Now Step 3)
    public $isIntelligenceLoaded = false;
    public $isLoadingIntelligence = false;
    public $aiSuggestedCompliances = []; // To store AI response
    public $groupedCompliances = []; // To display grouped by category
    public $selectedCompliances = []; // Selected by user
    public $ai_error = null; // Store AI errors
    public $collectedData = []; // Store document inputs (files/text)

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

        if ($this->step === 3) { // WAS 4
            return [
                'selectedCompliances' => 'array',
            ];
        }

        return [];
    }

    public function mount()
    {
        $this->businessTypes = CABusinessType::where('status', 'active')->get();
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

        // We no longer call loadIntelligence synchronously on Step 2.
        
        if ($this->step === 3) { // WAS 4
            if (empty($this->selectedCompliances)) {
                $this->addError('selectedCompliances', 'Please select at least one compliance requirement.');
                return;
            }
        }

        if ($this->step === 4) { // WAS 5
            // Validate collectedData against 'Required Now' expected documents
            $requiredDocs = $this->expectedDocuments['Required Now'] ?? collect();
            foreach ($requiredDocs as $doc) {
                if (!isset($this->collectedData[$doc->id]) || empty($this->collectedData[$doc->id])) {
                    $this->addError('collectedData.'.$doc->id, 'This document/input is required.');
                    return;
                }
            }
        }

        if ($this->step === 5) { // WAS 6
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
            $this->js('document.querySelector("main").scrollTo({ top: 0, behavior: "smooth" })');
        }
    }

    public function setStep($step)
    {
        $this->step = $step;
        $this->js('document.querySelector("main").scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function loadIntelligence()
    {
        sleep(2); // Artificial delay to ensure the AI animation plays

        $this->ai_error = null;
        $businessType = CABusinessType::find($this->business_type_id);
        
        try {
            $aiService = app(\Modules\CA\Services\AI\KnowledgeEngineService::class);
            $intelligence = $aiService->generateComplianceKnowledge($businessType->name);
            $this->aiSuggestedCompliances = $intelligence ?? [];
            
            // Populate database based on AI response
            if (!empty($intelligence) && isset($intelligence['service_categories'])) {
                $totalAiItems = 0;
                foreach ($intelligence['service_categories'] as $catData) {
                    $category = CAServiceCategory::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($catData['name'])],
                        [
                            'name' => $catData['name'],
                            'description' => $catData['description'] ?? null,
                            'sort_order' => 0
                        ]
                    );

                    foreach ($catData['compliances'] as $compData) {
                        $compliance = CACompliance::firstOrCreate(
                            ['slug' => \Illuminate\Support\Str::slug($compData['name'])],
                            [
                                'ca_service_category_id' => $category->id,
                                'name' => $compData['name'],
                                'description' => $compData['description'] ?? null,
                                'is_recurring' => $compData['is_recurring'] ?? false,
                            ]
                        );
                        
                        // Save requirements (documents)
                        if (isset($compData['requirements']) && is_array($compData['requirements'])) {
                            foreach ($compData['requirements'] as $reqData) {
                                \Modules\CA\Models\CAComplianceRequirement::firstOrCreate(
                                    [
                                        'ca_compliance_id' => $compliance->id,
                                        'slug' => \Illuminate\Support\Str::slug($reqData['name'])
                                    ],
                                    [
                                        'name' => $reqData['name'],
                                        'description' => $reqData['description'] ?? null,
                                        'requirement_type' => $reqData['requirement_type'] ?? 'document',
                                        'input_type' => $reqData['input_type'] ?? 'file',
                                        'is_required' => $reqData['is_required'] ?? true,
                                        'required_when' => $reqData['required_when'] ?? 'Required Now',
                                    ]
                                );
                            }
                        }

                        // Save master deadlines
                        $hasDeadlineData = isset($compData['frequency']) || isset($compData['due_day']) || isset($compData['due_month']);
                        if ($hasDeadlineData) {
                            $frequency = $compData['frequency'] ?? 'one_time';
                            $dueDay = $compData['due_day'] ?? null;
                            $dueMonth = $compData['due_month'] ?? null;
                            $desc = $compData['name'] . ' Due';

                            if (!empty($compData['deadlines']) && is_array($compData['deadlines'])) {
                                $desc = $compData['deadlines'][0]['deadline_name'] ?? $desc;
                            }

                            \Modules\CA\Models\CAComplianceDeadline::firstOrCreate(
                                [
                                    'ca_compliance_id' => $compliance->id,
                                    'frequency' => $frequency,
                                    'description' => $desc,
                                ],
                                [
                                    'due_day' => $dueDay,
                                    'due_month' => $dueMonth,
                                    'reminder_window' => 15,
                                    'status' => 'active',
                                ]
                            );
                        }

                        $businessType->compliances()->syncWithoutDetaching([$compliance->id]);
                        $totalAiItems++;
                    }
                }
                
                session()->flash('ai_success', "AI successfully generated {$totalAiItems} compliance requirements for this business type.");
            }
        } catch (Exception $e) {
            Log::error("Failed to load AI intelligence: " . $e->getMessage());
            $this->ai_error = "AI Intelligence failed to load: " . $e->getMessage();
            // Fallback: we still load database mapped compliances if AI fails
        }

        // Group compliances for display based on db mappings
        $this->groupedCompliances = CAServiceCategory::with(['compliances' => function($q) use ($businessType) {
            $q->whereHas('businessTypes', function($sq) use ($businessType) {
                $sq->where('ca_business_types.id', $businessType->id);
            })->where('status', 'active');
        }])->whereHas('compliances', function($q) use ($businessType) {
            $q->whereHas('businessTypes', function($sq) use ($businessType) {
                $sq->where('ca_business_types.id', $businessType->id);
            })->where('status', 'active');
        })->orderBy('sort_order')->get();

        // User must manually select the ones that apply to this business type
        $this->selectedCompliances = [];
        
        $this->isIntelligenceLoaded = true;
    }

    public function getExpectedDocumentsProperty()
    {
        if (empty($this->selectedCompliances)) {
            return collect();
        }

        return \Modules\CA\Models\CAComplianceRequirement::with('compliance')
            ->whereIn('ca_compliance_id', $this->selectedCompliances)
            ->where('requirement_type', 'document')
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                $first = $items->first();
                $first->compliance_names = $items->pluck('compliance.name')->unique()->filter()->implode(', ');
                return $first;
            })
            ->values()
            ->groupBy(function($item) {
                return $item->required_when ?? 'Required Now';
            });
    }

    public function submit()
    {
        $clientService = app(CAClientService::class);
        
        $actor = Auth::user();

        try {
            // 1. Create client
            $client = $clientService->createClient($actor, [
                'client_name' => $this->client_name,
                'phone' => $this->country_code . $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'notes' => $this->notes,
            ], $this->business_type_id);

            // 2. Assign compliances
            if (!empty($this->selectedCompliances)) {
                $clientService->assignCompliances($actor, $client, $this->selectedCompliances);
            }

            session()->flash('message', 'Client successfully onboarded!');
            
            // Redirect to detail page
            return redirect()->route('ca.clients.show', $client->id);
            
        } catch (Exception $e) {
            session()->flash('error', 'Error onboarding client: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('ca::livewire.client-onboarding-wizard')->layout('layouts.panel');
    }
}
