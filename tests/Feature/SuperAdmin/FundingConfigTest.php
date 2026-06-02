<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\FundingPackage;
use App\Models\SystemSetting;
use App\Models\PaymentTransaction;
use App\Models\CompanyPackage;
use App\Enums\PaymentTransactionStatus;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FundingConfigTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $companyOwner;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
            'company_id' => null,
        ]);

        $this->company = Company::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'primary_email' => 'owner@acme.com',
            'status' => 'active',
        ]);

        $this->companyOwner = User::factory()->create([
            'role' => 'user',
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);
    }

    /** @test */
    public function guests_are_redirected_to_login()
    {
        $this->get('/super-admin/funding')
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function standard_users_are_redirected_away()
    {
        $this->actingAs($this->companyOwner)
            ->get('/super-admin/funding')
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function super_admin_can_access_funding_config_page()
    {
        $this->actingAs($this->superAdmin)
            ->get('/super-admin/funding')
            ->assertOk()
            ->assertSee('Funding & Packages Config');
    }

    /** @test */
    public function super_admin_can_save_global_funding_settings()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\FundingConfig::class)
            ->set('wallet_threshold', 150.00)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertEquals(150.00, SystemSetting::get('wallet_threshold'));
    }

    /** @test */
    public function super_admin_can_create_update_toggle_and_delete_packages()
    {
        // 1. Create a Package
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\FundingConfig::class)
            ->call('openCreateModal')
            ->set('amount', 1200.00)
            ->set('text_rate', 0.08)
            ->set('template_utility_rate', 0.25)
            ->set('template_auth_rate', 0.12)
            ->set('template_marketing_rate', 0.45)
            ->set('automation_rate', 0.04)
            ->call('savePackage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('funding_packages', [
            'amount' => 1200.00,
            'text_rate' => 0.0800,
            'is_active' => true,
        ]);

        $package = FundingPackage::first();

        // 2. Update the Package
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\FundingConfig::class)
            ->call('openEditModal', $package->id)
            ->set('text_rate', 0.07)
            ->call('savePackage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('funding_packages', [
            'id' => $package->id,
            'text_rate' => 0.0700,
        ]);

        // 3. Toggle Status
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\FundingConfig::class)
            ->call('togglePackageStatus', $package->id);

        $this->assertFalse($package->fresh()->is_active);

        // 4. Delete the Package
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\FundingConfig::class)
            ->call('deletePackage', $package->id);

        $this->assertDatabaseMissing('funding_packages', [
            'id' => $package->id,
        ]);
    }

    /** @test */
    public function package_based_recharge_validation_and_creation_works()
    {
        // Setup settings and active package
        SystemSetting::set('minimum_recharge', 400.00);
        $package = FundingPackage::create([
            'amount' => 1000.00,
            'text_rate' => 0.05,
            'template_utility_rate' => 0.15,
            'template_auth_rate' => 0.10,
            'template_marketing_rate' => 0.35,
            'automation_rate' => 0.03,
            'is_active' => true,
        ]);

        // Test with active package selected
        Livewire::actingAs($this->companyOwner)
            ->test(\App\Livewire\Wallet\FundWalletModal::class)
            ->call('open')
            ->assertSet('selectedPackageId', $package->id)
            ->set('gateway', 'razorpay')
            ->call('initializeFunding')
            ->assertHasNoErrors();

        // Create transaction mock
        $transaction = PaymentTransaction::create([
            'user_id' => $this->companyOwner->id,
            'gateway' => \App\Enums\PaymentGateway::RAZORPAY,
            'type' => \App\Enums\PaymentTransactionType::WALLET_FUNDING,
            'amount' => 1000.00,
            'currency' => 'INR',
            'status' => \App\Enums\PaymentTransactionStatus::PENDING,
        ]);

        // Finalize transaction through payment service
        $paymentService = app(PaymentService::class);
        $paymentService->finalizeSuccessfulFunding(
            $transaction,
            'pay_test_123',
            'sig_test_123',
            ['mock' => true]
        );

        // Assert record created in company_packages
        $this->assertDatabaseHas('company_packages', [
            'company_id' => $this->company->id,
            'payment_transaction_id' => $transaction->id,
            'amount' => 1000.00,
            'remaining_balance' => 1000.00,
            'text_rate' => 0.0500,
            'template_utility_rate' => 0.1500,
            'status' => 'active',
        ]);
    }
}
