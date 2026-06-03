<?php

namespace App\Services\Platform;

use App\Models\Module;
use App\Models\CompanyModule;
use App\Models\Company;
use Nwidart\Modules\Facades\Module as LaravelModule;

class ModuleService
{
    /**
     * Sync discovered modules from the filesystem to the database registry.
     */
    public function syncDiscovery(): void
    {
        $allModules = LaravelModule::all();

        foreach ($allModules as $laravelModule) {
            Module::updateOrCreate(
                ['slug' => strtolower($laravelModule->getName())],
                [
                    'name' => $laravelModule->getName(),
                    'description' => $laravelModule->getDescription() ?? 'Module generated dynamically.',
                    'version' => $laravelModule->get('version', '1.0.0'),
                    'is_core' => false,
                ]
            );
        }

        // Add standard core modules for documentation/sidebar if they aren't there
        $coreModules = [
            'chats' => 'Conversations and messaging',
            'contacts' => 'Contacts and target groups',
            'campaigns' => 'Marketing and templates',
            'automations' => 'Workflow builder and simulations',
            'whatsapp' => 'WhatsApp and phone integrations',
            'wallet' => 'Payment and billing',
        ];

        foreach ($coreModules as $slug => $desc) {
            Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucfirst($slug),
                    'description' => $desc,
                    'version' => '1.0.0',
                    'is_core' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Enable a module.
     */
    public function enableModule(string $slug): bool
    {
        $module = Module::where('slug', $slug)->firstOrFail();
        $module->update(['is_active' => true]);

        // If it's a Laravel Module package, enable it there too
        if (!$module->is_core && LaravelModule::has($module->name)) {
            LaravelModule::enable($module->name);
        }

        return true;
    }

    /**
     * Disable a module.
     */
    public function disableModule(string $slug): bool
    {
        $module = Module::where('slug', $slug)->firstOrFail();
        if ($module->is_core) {
            return false;
        }

        $module->update(['is_active' => false]);

        if (LaravelModule::has($module->name)) {
            LaravelModule::disable($module->name);
        }

        return true;
    }

    /**
     * Retrieve all modules.
     */
    public function getAllModules()
    {
        $this->syncDiscovery();
        return Module::orderBy('is_core', 'desc')->orderBy('name', 'asc')->get();
    }

    /**
     * Assign a module to a company.
     */
    public function assignModuleToCompany(Company $company, string $moduleSlug, ?string $expiresAt = null): CompanyModule
    {
        $module = Module::where('slug', $moduleSlug)->firstOrFail();

        return CompanyModule::updateOrCreate(
            [
                'company_id' => $company->id,
                'module_id' => $module->id,
            ],
            [
                'status' => 'active',
                'enabled_at' => now(),
                'expires_at' => $expiresAt ? now()->parse($expiresAt) : null,
            ]
        );
    }

    /**
     * Remove a module from a company.
     */
    public function removeModuleFromCompany(Company $company, string $moduleSlug): void
    {
        $module = Module::where('slug', $moduleSlug)->firstOrFail();
        CompanyModule::where('company_id', $company->id)->where('module_id', $module->id)->delete();
    }

    /**
     * Validate if a company has active access to a module.
     */
    public function companyHasModule(Company $company, string $moduleSlug): bool
    {
        $module = Module::where('slug', $moduleSlug)->first();
        if (!$module) {
            return false;
        }

        if (!$module->is_active) {
            return false;
        }

        if ($module->is_core) {
            return true;
        }

        $assignment = CompanyModule::where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->first();

        if (!$assignment || $assignment->status !== 'active') {
            return false;
        }

        if ($assignment->expires_at && $assignment->expires_at->isPast()) {
            $assignment->update(['status' => 'expired']);
            return false;
        }

        return true;
    }
}
