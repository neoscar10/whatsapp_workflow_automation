<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_are_redirected_to_login()
    {
        $this->get(route('superadmin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('superadmin.companies'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function standard_users_are_redirected_away_from_super_admin_routes()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'active',
        ]);
        $user = User::factory()->create([
            'role' => 'user',
            'company_id' => $company->id,
        ]);

        $this->actingAs($user)
            ->get(route('superadmin.dashboard'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function super_admin_can_access_dashboard_and_companies_routes()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
            'company_id' => null,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('superadmin.companies'))
            ->assertOk();
    }

    /** @test */
    public function super_admin_role_cannot_be_changed()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The super_admin role cannot be modified.');

        $superAdmin->update(['role' => 'user']);
    }

    /** @test */
    public function super_admin_email_cannot_be_changed()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The super_admin user email cannot be modified.');

        $superAdmin->update(['email' => 'hacked@platform.local']);
    }

    /** @test */
    public function super_admin_user_cannot_be_deleted()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The super_admin user cannot be deleted.');

        $superAdmin->delete();
    }

    /** @test */
    public function wallet_service_debits_and_credits_demo_credits_when_company_is_in_demo_mode()
    {
        $company = Company::create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'primary_email' => 'demo@company.com',
            'status' => 'demo',
            'demo_credits' => 500.00,
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'company_id' => $company->id,
        ]);

        $walletService = app(\App\Services\Wallet\WalletService::class);
        $wallet = $walletService->getOrCreateWallet($user);

        // Balance of wallet remains 0
        $this->assertEquals(0, $wallet->balance);

        // Check hasSufficientBalance uses company demo credits
        $this->assertTrue($walletService->hasSufficientBalance($wallet, 300.00));
        $this->assertFalse($walletService->hasSufficientBalance($wallet, 600.00));

        // Debit should deduct company demo credits
        $walletService->debit($wallet, 200.00, \App\Enums\WalletTransactionCategory::USAGE);
        $company->refresh();
        $this->assertEquals(300.00, $company->demo_credits);

        // Credit should add company demo credits
        $walletService->credit($wallet, 100.00, \App\Enums\WalletTransactionCategory::FUNDING);
        $company->refresh();
        $this->assertEquals(400.00, $company->demo_credits);
    }

    /** @test */
    public function super_admin_can_impersonate_company_owner_and_exit_impersonation()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $company = Company::create([
            'name' => 'Impersonated Company',
            'slug' => 'impersonated-company',
            'primary_email' => 'owner@company.com',
            'status' => 'active',
        ]);

        $owner = User::factory()->create([
            'role' => 'user',
            'company_id' => $company->id,
            'is_company_owner' => true,
        ]);

        // Start impersonation simulation
        $this->actingAs($superAdmin);

        // Access the stop impersonating route, should redirect if not impersonating
        $this->get(route('superadmin.stop-impersonating'))
            ->assertRedirect(route('dashboard'));

        // Manually place the original admin ID in session and login as owner
        session(['impersonator_user_id' => $superAdmin->id]);
        $this->actingAs($owner);

        // Perform HTTP request to exit impersonation
        $this->get(route('superadmin.stop-impersonating'))
            ->assertRedirect(route('superadmin.companies'));

        // Confirm authenticated user is restored to super admin
        $this->assertEquals($superAdmin->id, auth()->id());
    }

    /** @test */
    public function demo_mode_expires_automatically_after_duration_elapses()
    {
        $company = Company::create([
            'name' => 'Temporary Demo Company',
            'slug' => 'temp-demo-company',
            'primary_email' => 'demo-expired@company.com',
            'status' => 'demo',
            'demo_credits' => 100.00,
            'demo_ends_at' => now()->addMinutes(5),
            'demo_whatsapp_phone_number_id' => null,
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

        // Verify it is in demo mode initially
        $this->assertEquals('demo', $company->fresh()->status);

        // Travel time forward by 6 minutes
        $this->travelTo(now()->addMinutes(6));

        // Make any web request to trigger the CheckCompanyDemoStatus middleware
        $this->get(route('dashboard'));

        $company->refresh();

        // Verify status reverted to active and details cleared
        $this->assertEquals('active', $company->status);
        $this->assertNull($company->demo_ends_at);
        $this->assertNull($company->demo_whatsapp_phone_number_id);
    }
}
