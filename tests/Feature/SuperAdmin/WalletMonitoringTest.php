<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Enums\WalletTransactionType;
use App\Enums\WalletTransactionCategory;
use App\Enums\WalletTransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WalletMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $companyOwner;
    protected Company $company;
    protected Wallet $wallet;

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

        $walletService = app(\App\Services\Wallet\WalletService::class);
        $this->wallet = $walletService->getOrCreateWallet($this->companyOwner);

        // Seed a transaction
        WalletTransaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => WalletTransactionType::CREDIT,
            'category' => WalletTransactionCategory::FUNDING,
            'amount' => 500.00,
            'balance_before' => 0.00,
            'balance_after' => 500.00,
            'reference' => 'TXN_TEST123',
            'status' => WalletTransactionStatus::SUCCESSFUL,
            'description' => 'Test deposit',
        ]);
        $this->wallet->update(['balance' => 500.00]);
    }

    /** @test */
    public function guests_are_redirected_to_login()
    {
        $this->get(route('superadmin.wallets'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function standard_users_are_redirected_away()
    {
        $this->actingAs($this->companyOwner)
            ->get(route('superadmin.wallets'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function super_admin_can_access_wallets_page()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.wallets'))
            ->assertOk()
            ->assertSee('Wallet Monitoring');
    }

    /** @test */
    public function super_admin_can_search_wallets_by_company_name()
    {
        $otherCompany = Company::create([
            'name' => 'Stark Industries',
            'slug' => 'stark-industries',
            'primary_email' => 'tony@stark.com',
            'status' => 'active',
        ]);
        $otherOwner = User::factory()->create([
            'role' => 'user',
            'company_id' => $otherCompany->id,
            'is_company_owner' => true,
        ]);
        $otherWallet = app(\App\Services\Wallet\WalletService::class)->getOrCreateWallet($otherOwner);

        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\WalletIndex::class)
            ->set('search', 'Acme')
            ->assertSee('Acme Corp')
            ->assertDontSee('Stark Industries');
    }

    /** @test */
    public function super_admin_can_open_wallet_ledger_details_modal()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\WalletIndex::class)
            ->call('viewDetails', $this->wallet->id)
            ->assertSet('showDetailsModal', true)
            ->assertSet('selectedWallet.id', $this->wallet->id)
            ->assertSee('TXN_TEST123')
            ->assertSee('Test deposit')
            ->call('closeModal')
            ->assertSet('showDetailsModal', false)
            ->assertSet('selectedWallet', null);
    }
}
