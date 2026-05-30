<?php

namespace Tests\Feature\Wallet;

use App\Livewire\Wallet\FundWalletModal;
use App\Livewire\Wallet\WalletDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WalletUiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_can_render_wallet_dashboard_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('wallet.index'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(WalletDashboard::class);
    }

    public function test_wallet_dashboard_displays_stats(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WalletDashboard::class)
            ->assertSee('Current Balance')
            ->assertSee('Total Funded')
            ->assertSee('Activity');
    }

    public function test_can_trigger_funding_modal_initialization(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FundWalletModal::class)
            ->set('amount', 500)
            ->set('gateway', 'razorpay')
            ->call('initializeFunding')
            ->assertHasNoErrors()
            ->assertDispatched('launch-razorpay');
    }

    public function test_cannot_initialize_with_invalid_amount(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FundWalletModal::class)
            ->set('amount', 5)
            ->set('gateway', 'razorpay')
            ->call('initializeFunding')
            ->assertHasErrors(['amount']);
    }
}
