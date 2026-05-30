<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Enums\WalletTransactionCategory;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
    }

    /** @test */
    public function unauthenticated_requests_are_rejected()
    {
        $response = $this->getJson(route('api.v1.wallet.show'));
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_retrieve_wallet_details()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('api.v1.wallet.show'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'balance',
                    'currency',
                    'status',
                    'last_transaction_at',
                    'total_funded',
                    'total_spent',
                ]
            ]);
    }

    /** @test */
    public function user_can_list_paginated_transactions_with_filters()
    {
        Sanctum::actingAs($this->user);

        $walletService = app(WalletService::class);
        $wallet = $walletService->getOrCreateWallet($this->user);

        // Seed transactions
        $walletService->credit($wallet, '100.0000', WalletTransactionCategory::FUNDING, 'Credit test');
        $walletService->debit($wallet, '40.0000', WalletTransactionCategory::USAGE, 'Debit test');

        $response = $this->getJson(route('api.v1.wallet.transactions', ['type' => 'credit']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.type', 'credit');
    }

    /** @test */
    public function user_can_view_single_transaction_details()
    {
        Sanctum::actingAs($this->user);

        $walletService = app(WalletService::class);
        $wallet = $walletService->getOrCreateWallet($this->user);
        $txn = $walletService->credit($wallet, '100.0000', WalletTransactionCategory::FUNDING, 'Credit test');

        $response = $this->getJson(route('api.v1.wallet.transactions.show', ['id' => $txn->id]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $txn->id);
    }

    /** @test */
    public function user_cannot_view_another_users_transaction_details()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $walletService = app(WalletService::class);
        $otherWallet = $walletService->getOrCreateWallet($otherUser);
        $txn = $walletService->credit($otherWallet, '50.0000', WalletTransactionCategory::FUNDING, 'Other credit');

        $response = $this->getJson(route('api.v1.wallet.transactions.show', ['id' => $txn->id]));

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_retrieve_funding_methods()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('api.v1.wallet.funding-methods'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'gateway',
                        'enabled',
                    ]
                ]
            ]);
    }
}
