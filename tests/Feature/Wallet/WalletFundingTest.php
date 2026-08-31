<?php

namespace Tests\Feature\Wallet;

use App\Enums\PaymentGateway;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletFundingTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;
    protected WalletService $walletService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paymentService = $this->app->make(PaymentService::class);
        $this->walletService = $this->app->make(WalletService::class);
        $this->user = User::factory()->create();
    }

    public function test_user_can_initialize_funding(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 150.00,
            'gateway' => 'razorpay'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_id',
                'gateway',
                'gateway_order_id',
                'amount',
                'currency',
                'checkout_data'
            ]
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $this->user->id,
            'amount' => '150.0000',
            'status' => 'pending',
            'gateway' => 'razorpay'
        ]);
    }

    public function test_cannot_initialize_funding_with_less_than_minimum_amount(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 5.00,
            'gateway' => 'razorpay'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_user_can_verify_funding_and_receive_credit(): void
    {
        Sanctum::actingAs($this->user);

        // Initialize first
        $initData = $this->paymentService->initializeWalletFunding($this->user, 200.00, PaymentGateway::RAZORPAY);
        $transactionId = $initData['transaction_id'];

        $response = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'razorpay_payment_id' => 'pay_mock123456',
            'razorpay_order_id' => $initData['gateway_order_id'],
            'razorpay_signature' => 'valid_mock_signature'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment verified and wallet credited successfully.'
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => 'pay_mock123456'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('200.0000', $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => '200.0000',
            'status' => 'successful',
            'category' => 'funding'
        ]);
    }

    public function test_funding_verification_is_idempotent(): void
    {
        Sanctum::actingAs($this->user);

        $initData = $this->paymentService->initializeWalletFunding($this->user, 100.00, PaymentGateway::RAZORPAY);
        $transactionId = $initData['transaction_id'];

        // Verify first time
        $response1 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'razorpay_payment_id' => 'pay_mock123456',
            'razorpay_order_id' => $initData['gateway_order_id'],
            'razorpay_signature' => 'valid_mock_signature'
        ]);
        $response1->assertStatus(200);

        // Verify second time (should skip credit, returning success response without double crediting)
        $response2 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'razorpay_payment_id' => 'pay_mock123456',
            'razorpay_order_id' => $initData['gateway_order_id'],
            'razorpay_signature' => 'valid_mock_signature'
        ]);
        $response2->assertStatus(200);

        // Check balance is only credited once
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('100.0000', $wallet->balance);
    }

    public function test_user_can_initialize_funding_with_cashfree(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 150.00,
            'gateway' => 'cashfree'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_id',
                'gateway',
                'gateway_order_id',
                'amount',
                'currency',
                'checkout_data' => [
                    'payment_session_id',
                    'order_id',
                    'environment'
                ]
            ]
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $this->user->id,
            'amount' => '150.0000',
            'status' => 'pending',
            'gateway' => 'cashfree'
        ]);
    }

    public function test_user_can_verify_funding_with_cashfree_and_receive_credit(): void
    {
        Sanctum::actingAs($this->user);

        // Initialize first
        $initData = $this->paymentService->initializeWalletFunding($this->user, 300.00, PaymentGateway::CASHFREE);
        $transactionId = $initData['transaction_id'];

        $response = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'cf_payment_id' => 'cf_pay_mock123456',
            'cf_signature' => 'valid_cf_mock_signature'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment verified and wallet credited successfully.'
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => 'cf_pay_mock123456'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('300.0000', $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => '300.0000',
            'status' => 'successful',
            'category' => 'funding'
        ]);
    }

    public function test_cashfree_funding_verification_is_idempotent(): void
    {
        Sanctum::actingAs($this->user);

        $initData = $this->paymentService->initializeWalletFunding($this->user, 150.00, PaymentGateway::CASHFREE);
        $transactionId = $initData['transaction_id'];

        // Verify first time
        $response1 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'cf_payment_id' => 'cf_pay_mock123456',
            'cf_signature' => 'valid_cf_mock_signature'
        ]);
        $response1->assertStatus(200);

        // Verify second time (should skip credit, returning success response without double crediting)
        $response2 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'cf_payment_id' => 'cf_pay_mock123456',
            'cf_signature' => 'valid_cf_mock_signature'
        ]);
        $response2->assertStatus(200);

        // Check balance is only credited once
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('150.0000', $wallet->balance);
    }

    public function test_user_can_initialize_funding_with_payu(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.wallet.fund.initialize'), [
            'amount' => 500.00,
            'gateway' => 'payu'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_id',
                'gateway',
                'gateway_order_id',
                'amount',
                'currency',
                'checkout_data' => [
                    'action_url',
                    'params' => [
                        'key',
                        'txnid',
                        'amount',
                        'productinfo',
                        'firstname',
                        'email',
                        'phone',
                        'surl',
                        'furl',
                        'hash',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $this->user->id,
            'amount' => '500.0000',
            'status' => 'pending',
            'gateway' => 'payu'
        ]);
    }

    public function test_user_can_verify_funding_with_payu_and_receive_credit(): void
    {
        Sanctum::actingAs($this->user);

        // Initialize first
        $initData = $this->paymentService->initializeWalletFunding($this->user, 500.00, PaymentGateway::PAYU);
        $transactionId = $initData['transaction_id'];

        $response = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'mihpayid' => 'payu_pay_mock123456',
            'hash' => 'valid_mock_signature',
            'status' => 'success',
            'txnid' => $transactionId
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment verified and wallet credited successfully.'
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => 'payu_pay_mock123456'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('500.0000', $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => '500.0000',
            'status' => 'successful',
            'category' => 'funding'
        ]);
    }

    public function test_payu_funding_verification_is_idempotent(): void
    {
        Sanctum::actingAs($this->user);

        $initData = $this->paymentService->initializeWalletFunding($this->user, 250.00, PaymentGateway::PAYU);
        $transactionId = $initData['transaction_id'];

        // Verify first time
        $response1 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'mihpayid' => 'payu_pay_mock123456',
            'hash' => 'valid_mock_signature',
            'status' => 'success',
            'txnid' => $transactionId
        ]);
        $response1->assertStatus(200);

        // Verify second time
        $response2 = $this->postJson(route('api.v1.wallet.fund.verify', ['transactionId' => $transactionId]), [
            'mihpayid' => 'payu_pay_mock123456',
            'hash' => 'valid_mock_signature',
            'status' => 'success',
            'txnid' => $transactionId
        ]);
        $response2->assertStatus(200);

        // Check balance is only credited once
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('250.0000', $wallet->balance);
    }

    public function test_payu_browser_post_callback_verifies_and_redirects(): void
    {
        $initData = $this->paymentService->initializeWalletFunding($this->user, 500.00, PaymentGateway::PAYU);
        $transactionId = $initData['transaction_id'];

        $response = $this->post(route('payment.payu.callback'), [
            'status' => 'success',
            'txnid' => $transactionId,
            'mihpayid' => '403993715535311234',
            'amount' => '500.00',
            'hash' => 'valid_mock_signature',
        ]);

        $response->assertRedirect(route('wallet.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => '403993715535311234'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('500.0000', $wallet->balance);
    }
}
