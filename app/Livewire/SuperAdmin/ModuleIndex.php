<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Module;
use App\Services\Platform\ModuleService;
use Livewire\Component;

class ModuleIndex extends Component
{
    public $search = '';

    public function toggleStatus(ModuleService $moduleService, $slug)
    {
        $module = Module::where('slug', $slug)->firstOrFail();
        if ($module->is_active) {
            $moduleService->disableModule($slug);
            session()->flash('success', "Module '{$module->name}' disabled successfully.");
        } else {
            $moduleService->enableModule($slug);
            session()->flash('success', "Module '{$module->name}' enabled successfully.");
        }
    }

    public function render(ModuleService $moduleService)
    {
        // Trigger discovery sync on list load
        $moduleService->syncDiscovery();

        $modules = Module::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('is_core', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.super-admin.module-index', [
            'modules' => $modules,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Module Management',
            'activeNav' => 'modules',
        ]);
    }
}
