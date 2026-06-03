<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\Module;
use App\Models\CompanyModule;
use App\Services\Platform\ModuleService;
use Livewire\Component;

class CompanyModuleAssignment extends Component
{
    public $search = '';
    public $selectedCompanyId = null;
    
    // Form fields
    public $moduleSlug = '';
    public $expiresAt = '';

    protected $rules = [
        'moduleSlug' => 'required|exists:modules,slug',
        'expiresAt' => 'nullable|date|after:today',
    ];

    public function selectCompany($id)
    {
        $this->selectedCompanyId = $id;
        $this->resetValidation();
        $this->reset(['moduleSlug', 'expiresAt']);
    }

    public function assignModule(ModuleService $moduleService)
    {
        $this->validate();

        $company = Company::findOrFail($this->selectedCompanyId);
        $moduleService->assignModuleToCompany($company, $this->moduleSlug, $this->expiresAt ?: null);

        session()->flash('success', "Module assigned successfully.");
        $this->reset(['moduleSlug', 'expiresAt']);
    }

    public function removeModule(ModuleService $moduleService, $slug)
    {
        $company = Company::findOrFail($this->selectedCompanyId);
        $moduleService->removeModuleFromCompany($company, $slug);

        session()->flash('success', "Module removed successfully.");
    }

    public function render()
    {
        $companies = Company::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('primary_email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->get();

        $selectedCompany = $this->selectedCompanyId ? Company::find($this->selectedCompanyId) : null;
        $assignedModules = [];
        if ($selectedCompany) {
            $assignedModules = CompanyModule::with('module')
                ->where('company_id', $selectedCompany->id)
                ->get();
        }

        // Only show non-core, active modules for assignment
        $availableModules = Module::where('is_core', false)
            ->where('is_active', true)
            ->get();

        return view('livewire.super-admin.company-module-assignment', [
            'companies' => $companies,
            'selectedCompany' => $selectedCompany,
            'assignedModules' => $assignedModules,
            'availableModules' => $availableModules,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Company Module Assignment',
            'activeNav' => 'modules',
        ]);
    }
}
