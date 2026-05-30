<?php

namespace Tests\Feature\Wallet;

use App\Enums\PaymentGateway;
use App\Enums\WalletStatus;
use App\Enums\WalletTransactionCategory;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\WalletOperationException;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletSystemTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;
    protected PaymentService $paymentService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletService = $this->app->make(WalletService::class);
        $this->paymentService = $this->app->make(PaymentService::class);
        
        $this->user = User::factory()->create();
    }

    public function test_can_get_or_create_wallet(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($this->user->id, $wallet->user_id);
        $this->assertEquals('0.0000', $wallet->balance);
        $this->assertEquals(WalletStatus::ACTIVE, $wallet->status);
        $this->assertEquals('INR', $wallet->currency);

        // Fetching again should return the same wallet
        $sameWallet = $this->walletService->getOrCreateWallet($this->user);
        $this->assertEquals($wallet->id, $sameWallet->id);
    }

    public function test_can_credit_wallet(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);

        $transaction = $this->walletService->credit(
            $wallet,
            '100.5000',
            WalletTransactionCategory::FUNDING,
            'Deposited funds via Razorpay',
            'pay_mock123',
            ['gateway' => 'razorpay']
        );

        $this->assertEquals(WalletTransactionType::CREDIT, $transaction->type);
        $this->assertEquals('100.5000', $transaction->amount);
        $this->assertEquals('0.0000', $transaction->balance_before);
        $this->assertEquals('100.5000', $transaction->balance_after);
        $this->assertEquals(WalletTransactionStatus::SUCCESSFUL, $transaction->status);
        $this->assertEquals('pay_mock123', $transaction->provider_reference);
        $this->assertEquals('Deposited funds via Razorpay', $transaction->description);
        $this->assertEquals(['gateway' => 'razorpay'], $transaction->metadata);

        // Refresh and check wallet balance
        $wallet->refresh();
        $this->assertEquals('100.5000', $wallet->balance);
        $this->assertNotNull($wallet->last_transaction_at);
    }

    public function test_can_debit_wallet(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);

        // First credit
        $this->walletService->credit($wallet, '150.0000', WalletTransactionCategory::FUNDING);

        // Perform debit
        $transaction = $this->walletService->debit(
            $wallet,
            '50.2500',
            WalletTransactionCategory::USAGE,
            'Campaign charge'
        );

        $this->assertEquals(WalletTransactionType::DEBIT, $transaction->type);
        $this->assertEquals('50.2500', $transaction->amount);
        $this->assertEquals('150.0000', $transaction->balance_before);
        $this->assertEquals('99.7500', $transaction->balance_after);
        $this->assertEquals(WalletTransactionStatus::SUCCESSFUL, $transaction->status);

        $wallet->refresh();
        $this->assertEquals('99.7500', $wallet->balance);
    }

    public function test_cannot_debit_with_insufficient_balance(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->walletService->credit($wallet, '10.0000', WalletTransactionCategory::FUNDING);

        $this->expectException(InsufficientWalletBalanceException::class);
        $this->walletService->debit($wallet, '15.0000', WalletTransactionCategory::USAGE);
    }

    public function test_has_sufficient_balance_helper(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->walletService->credit($wallet, '100.0000', WalletTransactionCategory::FUNDING);
        $wallet->refresh();

        $this->assertTrue($this->walletService->hasSufficientBalance($wallet, '100.0000'));
        $this->assertTrue($this->walletService->hasSufficientBalance($wallet, '50.0000'));
        $this->assertFalse($this->walletService->hasSufficientBalance($wallet, '100.0100'));
    }

    public function test_cannot_credit_negative_or_zero_amount(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->user);

        $this->expectException(WalletOperationException::class);
        $this->walletService->credit($wallet, '-5.0000', WalletTransactionCategory::FUNDING);
    }

    public function test_payment_service_resolves_gateway_drivers(): void
    {
        $razorpay = $this->paymentService->resolve(PaymentGateway::RAZORPAY);
        $this->assertInstanceOf(\App\Services\Payment\Gateways\RazorpayGatewayService::class, $razorpay);

        $cashfree = $this->paymentService->resolve(PaymentGateway::CASHFREE);
        $this->assertInstanceOf(\App\Services\Payment\Gateways\CashfreeGatewayService::class, $cashfree);

        $default = $this->paymentService->resolve();
        $this->assertInstanceOf(\App\Services\Payment\Gateways\RazorpayGatewayService::class, $default);
    }
}
