<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\Module;
use App\Models\CompanyModule;
use App\Models\User;
use App\Services\Platform\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Company $company;
    protected User $companyOwner;
    protected ModuleService $moduleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleService = app(ModuleService::class);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'owner@test.com',
            'status' => 'active',
            'country' => 'US',
        ]);

        $this->companyOwner = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'owner',
            'email' => 'owner@test.com',
        ]);
    }

    /** @test */
    public function super_admin_can_access_modules_index_page()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.modules'))
            ->assertOk()
            ->assertSee('Modules Management');
    }

    /** @test */
    public function super_admin_can_toggle_module_status()
    {
        $this->moduleService->syncDiscovery();

        $module = Module::where('slug', 'ca')->firstOrFail();
        $this->assertTrue($module->is_active);

        // Toggle to disabled
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\ModuleIndex::class)
            ->call('toggleStatus', 'ca')
            ->assertHasNoErrors();

        $this->assertFalse($module->fresh()->is_active);

        // Toggle back to enabled
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\ModuleIndex::class)
            ->call('toggleStatus', 'ca')
            ->assertHasNoErrors();

        $this->assertTrue($module->fresh()->is_active);
    }

    /** @test */
    public function tenant_users_blocked_from_ca_dashboard_when_not_assigned()
    {
        $this->moduleService->syncDiscovery();

        // Default: Company has no CA module assigned
        $this->actingAs($this->companyOwner)
            ->get(route('ca.dashboard'))
            ->assertStatus(403);
    }

    /** @test */
    public function tenant_users_can_access_ca_dashboard_when_assigned()
    {
        $this->moduleService->syncDiscovery();

        // Assign CA module to company
        $this->moduleService->assignModuleToCompany($this->company, 'ca');

        $this->actingAs($this->companyOwner)
            ->get(route('ca.dashboard'))
            ->assertOk()
            ->assertSee('CA Dashboard')
            ->assertSee('Module Verification Success!');
    }

    /** @test */
    public function super_admin_can_impersonate_and_assign_module_to_company()
    {
        $this->moduleService->syncDiscovery();

        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\CompanyModuleAssignment::class)
            ->call('selectCompany', $this->company->id)
            ->set('moduleSlug', 'ca')
            ->call('assignModule')
            ->assertHasNoErrors();

        $this->assertTrue($this->moduleService->companyHasModule($this->company, 'ca'));

        // Now remove it
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\CompanyModuleAssignment::class)
            ->call('selectCompany', $this->company->id)
            ->call('removeModule', 'ca')
            ->assertHasNoErrors();

        $this->assertFalse($this->moduleService->companyHasModule($this->company, 'ca'));
    }
}
