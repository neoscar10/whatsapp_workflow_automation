<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyLiveAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_toggle_live_access_on_companies_table()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\SuperAdmin\CompanyIndex::class)
            ->call('toggleLiveAccess', $company->id);

        $this->assertTrue($company->fresh()->can_access_live);

        // Toggle back off while active
        $company->update(['status' => 'active', 'can_access_live' => true]);

        Livewire::test(\App\Livewire\SuperAdmin\CompanyIndex::class)
            ->call('toggleLiveAccess', $company->id);

        $this->assertFalse($company->fresh()->can_access_live);
        $this->assertEquals('demo', $company->fresh()->status);
    }

    public function test_company_user_can_toggle_mode_when_live_access_is_enabled()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => true,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_admin',
        ]);

        $this->actingAs($user);

        // Toggle from demo to live
        Livewire::test(\App\Livewire\Web\Panel\TopbarCompanyModeSwitch::class)
            ->call('toggleMode');

        $this->assertEquals('active', $company->fresh()->status);

        // Toggle from live to demo
        Livewire::test(\App\Livewire\Web\Panel\TopbarCompanyModeSwitch::class)
            ->call('toggleMode');

        $this->assertEquals('demo', $company->fresh()->status);
    }

    public function test_company_user_cannot_toggle_mode_when_live_access_is_disabled()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => false,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_admin',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Web\Panel\TopbarCompanyModeSwitch::class)
            ->call('toggleMode');

        $this->assertEquals('demo', $company->fresh()->status);
    }
}
