<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CAAutomationLibrary;
use Modules\CA\Models\CAClientAutomation;
use Modules\CA\Models\CAClientAutomationRule;
use Modules\CA\Services\AutomationTemplateLibraryService;
use Modules\CA\Services\ReminderRuleService;
use Modules\CA\Services\TemplateManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AutomationLibraryPage extends Component
{
    public $activeTab = 'mine'; // 'mine' or 'system'
    public $selectedId;
    public $viewMode = 'list';  // 'list' or 'detail'
    public $showCreateModal = false;
    public $selectedLibraryId = null;

    public $customName = '';
    public $templateTitle = '';
    public $templateBody = '';
    public $overdueTitle = '';
    public $overdueBody = '';
    public $isFromCache = false;

    // AI Tone selection and regeneration
    public $selectedTone = 'professional';

    // Rules editing
    public array $editingRules = [];

    // Variables mapping config
    public array $variableMappings = ['header' => [], 'body' => []];
    public array $extractedVariables = ['header' => [], 'body' => []];

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
        $this->viewMode = 'list';
        $this->selectedId = null;
        $this->resetWorkspace();
    }

    public function selectAutomation($id)
    {
        $this->selectedId = $id;
        $this->loadDetails();
        $this->viewMode = 'detail';
    }

    public function goBackToList()
    {
        $this->viewMode = 'list';
    }

    public function loadDetails()
    {
        if (!$this->selectedId) {
            $this->resetWorkspace();
            return;
        }

        $companyId = Auth::user()->company_id;

        if ($this->activeTab === 'mine') {
            $automation = CAClientAutomation::where('company_id', $companyId)
                ->whereNull('client_id')
                ->findOrFail($this->selectedId);

            $this->customName = $automation->metadata_json['custom_name'] ?? '';
            $this->templateTitle = $automation->metadata_json['custom_message_title'] ?? '';
            $this->templateBody = $automation->metadata_json['custom_message_body'] ?? '';
            $this->overdueTitle = $automation->metadata_json['custom_overdue_message_title'] ?? '';
            $this->overdueBody = $automation->metadata_json['custom_overdue_message_body'] ?? '';
            
            if (empty($this->templateTitle) || empty($this->templateBody)) {
                $library = $automation->automationLibrary;
                $service = app(AutomationTemplateLibraryService::class);
                $result = $service->getOrGenerateTemplate($library, 'en', $this->selectedTone);
                $this->templateTitle = $result['message_title'] ?? '';
                $this->templateBody = $result['message_body'] ?? '';
            }

            if (empty($this->overdueTitle) || empty($this->overdueBody)) {
                $library = $automation->automationLibrary;
                $service = app(AutomationTemplateLibraryService::class);
                $result = $service->getOrGenerateTemplate($library, 'en', 'urgent', false, true);
                $this->overdueTitle = $result['message_title'] ?? '';
                $this->overdueBody = $result['message_body'] ?? '';
            }

            // Load rules
            $this->editingRules = $automation->rules->map(fn($rule) => [
                'id'           => $rule->id,
                'trigger_type' => $rule->trigger_type,
                'offset_days'  => $rule->offset_days,
                'send_time'    => $rule->send_time,
                'is_enabled'   => $rule->is_enabled,
            ])->toArray();

            $this->isFromCache = true;
            $this->extractVariablesFromText();

            // Load variable mappings
            $this->variableMappings = $automation->metadata_json['template_variable_mappings'] ?? ['header' => [], 'body' => []];
            $this->initializeVariableMappings();

        } else {
            $library = CAAutomationLibrary::findOrFail($this->selectedId);
            $this->customName = $library->name;

            $service = app(AutomationTemplateLibraryService::class);
            $result = $service->getOrGenerateTemplate($library, 'en', $this->selectedTone);

            $this->templateTitle = $result['message_title'] ?? '';
            $this->templateBody = $result['message_body'] ?? '';
            $this->isFromCache = $result['from_cache'] ?? false;

            // Load default system rules
            $defaultRules = app(ReminderRuleService::class)->getDefaultRules($library->frequency);
            $this->editingRules = array_map(fn($r) => [
                'trigger_type' => $r['trigger_type'],
                'offset_days'  => $r['offset_days'],
                'send_time'    => $r['send_time'],
                'is_enabled'   => $r['is_enabled'] ?? true,
            ], $defaultRules);

            // Load default overdue template
            $overdueResult = $service->getOrGenerateTemplate($library, 'en', 'urgent', false, true);
            $this->overdueTitle = $overdueResult['message_title'] ?? '';
            $this->overdueBody = $overdueResult['message_body'] ?? '';

            $this->extractVariablesFromText();
            $this->variableMappings = ['header' => [], 'body' => []];
            $this->initializeVariableMappings();
        }
    }

    public function updatedSelectedTone()
    {
        if ($this->activeTab === 'system' && $this->selectedId) {
            $library = CAAutomationLibrary::findOrFail($this->selectedId);
            $service = app(AutomationTemplateLibraryService::class);
            $result = $service->getOrGenerateTemplate($library, 'en', $this->selectedTone);
            $this->templateTitle = $result['message_title'] ?? '';
            $this->templateBody = $result['message_body'] ?? '';
            $this->extractVariablesFromText();
            $this->initializeVariableMappings();
        }
    }

    public function selectTone($tone)
    {
        $this->selectedTone = $tone;
        $this->updatedSelectedTone();
    }

    public function regenerateWithAI()
    {
        if (!$this->selectedId && !$this->showCreateModal) {
            return;
        }

        $library = null;
        if ($this->showCreateModal) {
            if (!$this->selectedLibraryId) return;
            $library = CAAutomationLibrary::findOrFail($this->selectedLibraryId);
        } elseif ($this->activeTab === 'mine') {
            $automation = CAClientAutomation::findOrFail($this->selectedId);
            $library = $automation->automationLibrary;
        } else {
            $library = CAAutomationLibrary::findOrFail($this->selectedId);
        }

        $sessionKey = 'ai_regen_count_' . $library->id;
        $count = session()->get($sessionKey, 0);

        if ($count >= 3) {
            session()->flash('error', 'You have reached the limit of 3 AI generations for this template.');
            return;
        }

        try {
            $service = app(AutomationTemplateLibraryService::class);
            $result = $service->getOrGenerateTemplate($library, 'en', $this->selectedTone, true, false);

            $this->templateTitle = $result['message_title'] ?? '';
            $this->templateBody = $result['message_body'] ?? '';
            $this->isFromCache = false;

            session()->put($sessionKey, $count + 1);
            $this->extractVariablesFromText();
            $this->initializeVariableMappings();

            session()->flash('success', 'Variation regenerated via AI successfully. (Attempt ' . ($count + 1) . '/3)');
        } catch (Exception $e) {
            session()->flash('error', 'Regeneration failed: ' . $e->getMessage());
        }
    }

    public function regenerateOverdueWithAI()
    {
        if (!$this->selectedId && !$this->showCreateModal) {
            return;
        }

        $library = null;
        if ($this->showCreateModal) {
            if (!$this->selectedLibraryId) return;
            $library = CAAutomationLibrary::findOrFail($this->selectedLibraryId);
        } elseif ($this->activeTab === 'mine') {
            $automation = CAClientAutomation::findOrFail($this->selectedId);
            $library = $automation->automationLibrary;
        } else {
            $library = CAAutomationLibrary::findOrFail($this->selectedId);
        }

        $sessionKey = 'ai_overdue_regen_count_' . $library->id;
        $count = session()->get($sessionKey, 0);

        if ($count >= 3) {
            session()->flash('error', 'You have reached the limit of 3 AI generations for the overdue template.');
            return;
        }

        try {
            $service = app(AutomationTemplateLibraryService::class);
            $result = $service->getOrGenerateTemplate($library, 'en', 'urgent', true, true);

            $this->overdueTitle = $result['message_title'] ?? '';
            $this->overdueBody = $result['message_body'] ?? '';

            session()->put($sessionKey, $count + 1);
            $this->extractVariablesFromText();
            $this->initializeVariableMappings();

            session()->flash('success', 'Overdue template regenerated via AI successfully. (Attempt ' . ($count + 1) . '/3)');
        } catch (Exception $e) {
            session()->flash('error', 'Overdue regeneration failed: ' . $e->getMessage());
        }
    }

    private function resetWorkspace()
    {
        $this->customName = '';
        $this->templateTitle = '';
        $this->templateBody = '';
        $this->overdueTitle = '';
        $this->overdueBody = '';
        $this->editingRules = [];
        $this->variableMappings = ['header' => [], 'body' => []];
        $this->extractedVariables = ['header' => [], 'body' => []];
    }

    public function extractVariablesFromText()
    {
        $this->extractedVariables = ['header' => [], 'body' => []];

        preg_match_all('/\{\{(.+?)\}\}/', $this->templateBody, $bodyMatches);
        if (!empty($bodyMatches[1])) {
            foreach (array_unique($bodyMatches[1]) as $var) {
                $this->extractedVariables['body'][$var] = $var;
            }
        }

        preg_match_all('/\{\{(.+?)\}\}/', $this->templateTitle, $headerMatches);
        if (!empty($headerMatches[1])) {
            foreach (array_unique($headerMatches[1]) as $var) {
                $this->extractedVariables['header'][$var] = $var;
            }
        }

        preg_match_all('/\{\{(.+?)\}\}/', $this->overdueBody, $bodyMatchesOverdue);
        if (!empty($bodyMatchesOverdue[1])) {
            foreach (array_unique($bodyMatchesOverdue[1]) as $var) {
                $this->extractedVariables['body'][$var] = $var;
            }
        }

        preg_match_all('/\{\{(.+?)\}\}/', $this->overdueTitle, $headerMatchesOverdue);
        if (!empty($headerMatchesOverdue[1])) {
            foreach (array_unique($headerMatchesOverdue[1]) as $var) {
                $this->extractedVariables['header'][$var] = $var;
            }
        }
    }

    private function initializeVariableMappings()
    {
        foreach ($this->extractedVariables['body'] as $var) {
            if (!isset($this->variableMappings['body'][$var])) {
                $this->variableMappings['body'][$var] = [
                    'source' => 'system',
                    'value'  => $var,
                ];
            }
        }

        foreach ($this->extractedVariables['header'] as $var) {
            if (!isset($this->variableMappings['header'][$var])) {
                $this->variableMappings['header'][$var] = [
                    'source' => 'system',
                    'value'  => $var,
                ];
            }
        }
    }

    public function updatedTemplateBody()
    {
        $this->extractVariablesFromText();
        $this->initializeVariableMappings();
    }

    public function updatedTemplateTitle()
    {
        $this->extractVariablesFromText();
        $this->initializeVariableMappings();
    }

    public function updatedOverdueBody()
    {
        $this->extractVariablesFromText();
        $this->initializeVariableMappings();
    }

    public function updatedOverdueTitle()
    {
        $this->extractVariablesFromText();
        $this->initializeVariableMappings();
    }

    public function addReminderRule()
    {
        $this->editingRules[] = [
            'trigger_type' => 'before_due',
            'offset_days'  => 1,
            'send_time'    => '09:00',
            'is_enabled'   => true,
        ];
    }

    public function removeReminderRule($index)
    {
        unset($this->editingRules[$index]);
        $this->editingRules = array_values($this->editingRules);
    }

    public function addToYourAutomations()
    {
        if ($this->activeTab !== 'system' || !$this->selectedId) {
            return;
        }

        $library = CAAutomationLibrary::findOrFail($this->selectedId);
        $companyId = Auth::user()->company_id;

        $exists = CAClientAutomation::where('company_id', $companyId)
            ->whereNull('client_id')
            ->where('automation_library_id', $library->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This automation is already in your library.');
            return;
        }

        $hasOverdue = collect($this->editingRules)->contains('trigger_type', 'after_due');

        $automation = CAClientAutomation::create([
            'company_id'            => $companyId,
            'client_id'             => null,
            'automation_library_id' => $library->id,
            'frequency'             => $library->frequency,
            'status'                => 'active',
            'is_enabled'            => true,
            'created_by'            => Auth::id(),
            'metadata_json'         => [
                'custom_name'                  => $this->customName ?: $library->name,
                'custom_message_title'         => $this->templateTitle,
                'custom_message_body'          => $this->templateBody,
                'custom_overdue_message_title' => $this->overdueTitle,
                'custom_overdue_message_body'  => $this->overdueBody,
                'template_variable_mappings'   => $this->variableMappings,
            ],
        ]);

        // Save customized rules
        app(ReminderRuleService::class)->saveRules($automation->id, $this->editingRules);

        // Provision/deduplicate on Meta WABA
        try {
            app(TemplateManagementService::class)->resolveTemplateForAutomation($automation);
            if ($hasOverdue) {
                app(TemplateManagementService::class)->resolveOverdueTemplateForAutomation($automation);
            }
        } catch (Exception $e) {
            Log::warning("Immediate WABA template resolution failed: " . $e->getMessage());
        }

        session()->flash('success', 'Successfully added to Your Automations!');
        $this->activeTab = 'mine';
        $this->selectedId = null;
        $this->viewMode = 'list';
        $this->resetWorkspace();
    }

    public function openCreateModal(): void
    {
        $this->resetWorkspace();
        $this->showCreateModal = true;
        $this->selectedLibraryId = null;
        $this->editingRules = [
            [
                'trigger_type' => 'before_due',
                'offset_days'  => 1,
                'send_time'    => '09:00',
                'is_enabled'   => true,
            ]
        ];
        $this->extractVariablesFromText();
        $this->initializeVariableMappings();
    }

    public function updatedSelectedLibraryId(): void
    {
        if ($this->selectedLibraryId) {
            $library = CAAutomationLibrary::find($this->selectedLibraryId);
            if ($library) {
                $this->customName = $library->name;
            }
        }
    }

    public function createCustomAutomation(): void
    {
        $this->validate([
            'selectedLibraryId' => 'required|exists:ca_automation_library,id',
            'customName'        => 'required|string|max:255',
            'templateTitle'     => 'required|string|max:255',
            'templateBody'      => 'required|string',
        ]);

        // Validate rules
        try {
            app(ReminderRuleService::class)->validate($this->editingRules);
        } catch (Exception $e) {
            $this->addError('editingRules', $e->getMessage());
            return;
        }

        $library = CAAutomationLibrary::findOrFail($this->selectedLibraryId);
        $companyId = Auth::user()->company_id;

        $exists = CAClientAutomation::where('company_id', $companyId)
            ->whereNull('client_id')
            ->where('automation_library_id', $library->id)
            ->exists();

        if ($exists) {
            $this->addError('selectedLibraryId', 'An automation for this document category is already in your library.');
            return;
        }

        $hasOverdue = collect($this->editingRules)->contains('trigger_type', 'after_due');

        $automation = CAClientAutomation::create([
            'company_id'            => $companyId,
            'client_id'             => null,
            'automation_library_id' => $library->id,
            'frequency'             => $library->frequency,
            'status'                => 'active',
            'is_enabled'            => true,
            'created_by'            => Auth::id(),
            'metadata_json'         => [
                'custom_name'                  => $this->customName,
                'custom_message_title'         => $this->templateTitle,
                'custom_message_body'          => $this->templateBody,
                'custom_overdue_message_title' => $this->overdueTitle,
                'custom_overdue_message_body'  => $this->overdueBody,
                'template_variable_mappings'   => $this->variableMappings,
            ],
        ]);

        // Save rules
        app(ReminderRuleService::class)->saveRules($automation->id, $this->editingRules);

        // Provision/deduplicate on Meta WABA
        try {
            app(TemplateManagementService::class)->resolveTemplateForAutomation($automation);
            if ($hasOverdue) {
                app(TemplateManagementService::class)->resolveOverdueTemplateForAutomation($automation);
            }
        } catch (Exception $e) {
            Log::warning("Meta WABA template resolution failed: " . $e->getMessage());
        }

        session()->flash('success', 'Successfully created custom automation!');
        $this->showCreateModal = false;
        $this->resetWorkspace();
        $this->activeTab = 'mine';
        $this->viewMode = 'list';
    }

    public function saveChanges()
    {
        if ($this->activeTab !== 'mine' || !$this->selectedId) {
            return;
        }

        $companyId = Auth::user()->company_id;
        $automation = CAClientAutomation::where('company_id', $companyId)
            ->whereNull('client_id')
            ->findOrFail($this->selectedId);

        // Validate rules
        try {
            app(ReminderRuleService::class)->validate($this->editingRules);
        } catch (Exception $e) {
            $this->addError('editingRules', $e->getMessage());
            return;
        }

        // Save rules
        app(ReminderRuleService::class)->saveRules($automation->id, $this->editingRules);

        $hasOverdue = collect($this->editingRules)->contains('trigger_type', 'after_due');

        // Update metadata
        $automation->update([
            'metadata_json' => [
                'custom_name'                  => $this->customName ?: $automation->automationLibrary->name,
                'custom_message_title'         => $this->templateTitle,
                'custom_message_body'          => $this->templateBody,
                'custom_overdue_message_title' => $this->overdueTitle,
                'custom_overdue_message_body'  => $this->overdueBody,
                'template_variable_mappings'   => $this->variableMappings,
            ],
        ]);

        // Re-resolve/provision on Meta WABA
        try {
            app(TemplateManagementService::class)->resolveTemplateForAutomation($automation);
            if ($hasOverdue) {
                app(TemplateManagementService::class)->resolveOverdueTemplateForAutomation($automation);
            }
        } catch (Exception $e) {
            Log::warning("Meta WABA template resolution failed on save: " . $e->getMessage());
        }

        session()->flash('success', 'Changes saved successfully.');
        $this->loadDetails();
    }

    public function deleteCompanyAutomation($id)
    {
        $companyId = Auth::user()->company_id;
        $automation = CAClientAutomation::where('company_id', $companyId)
            ->whereNull('client_id')
            ->findOrFail($id);

        $automation->rules()->delete();
        $automation->delete();

        session()->flash('success', 'Automation deleted from your library.');
        $this->selectedId = null;
        $this->viewMode = 'list';
    }

    public function getAvailableSystemVariables()
    {
        return [
            ['key' => 'client_name', 'label' => 'Client Name'],
            ['key' => 'firm_name', 'label' => 'CA Firm Name'],
            ['key' => 'document_name', 'label' => 'Document Name'],
            ['key' => 'compliance_name', 'label' => 'Compliance Name'],
            ['key' => 'due_date', 'label' => 'Next Due Date'],
            ['key' => 'days_remaining', 'label' => 'Days Remaining'],
            ['key' => 'upload_link', 'label' => 'Client Upload Link'],
            ['key' => 'business_type', 'label' => 'Business Entity Type'],
        ];
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        
        $myAutomations = CAClientAutomation::where('company_id', $companyId)
            ->whereNull('client_id')
            ->get();

        $systemLibrary = CAAutomationLibrary::active()->get();

        $selectedAutomation = null;
        if ($this->selectedId) {
            $selectedAutomation = $this->activeTab === 'mine'
                ? CAClientAutomation::find($this->selectedId)
                : CAAutomationLibrary::find($this->selectedId);
        }

        return view('ca::livewire.automation-library-page', [
            'myAutomations'      => $myAutomations,
            'systemLibrary'      => $systemLibrary,
            'selectedAutomation' => $selectedAutomation,
        ])->layout('layouts.panel');
    }
}
