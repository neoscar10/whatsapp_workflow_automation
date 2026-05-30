<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\PaymentTransaction;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FundingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'funding@example.com',
            'password' => bcrypt('password123')
        ]);
    }

    /** @test */
    public function authenticated_user_can_initialize_wallet_funding()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 500.00,
            'gateway' => 'razorpay'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'transaction_id',
                    'gateway',
                    'gateway_order_id',
                    'amount',
                    'currency',
                    'checkout_data' => [
                        'key',
                        'amount',
                        'currency',
                        'order_id'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'status' => PaymentTransactionStatus::PENDING->value
        ]);
    }

    /** @test */
    public function authenticated_user_can_verify_funding_via_mock()
    {
        Sanctum::actingAs($this->user);

        // 1. Initialize
        $initResponse = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 150.00,
            'gateway' => 'razorpay'
        ]);

        $transactionId = $initResponse->json('data.transaction_id');
        $orderId = $initResponse->json('data.gateway_order_id');

        // 2. Verify with mock signature params
        $verifyResponse = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'razorpay_payment_id' => 'pay_mock_123',
            'razorpay_order_id' => $orderId,
            'razorpay_signature' => 'valid_mock_signature'
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'successful');

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => PaymentTransactionStatus::SUCCESSFUL->value
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'balance' => '150.0000'
        ]);
    }
}
