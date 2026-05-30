<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Models\PaymentTransaction;
use App\Enums\PaymentGateway;
use App\Enums\PaymentTransactionStatus;
use App\Services\Payment\PaymentService;
use App\Services\Payment\Reconciliation\PendingPaymentReconciliationService;
use App\Services\Payment\Reconciliation\AbandonedPaymentService;
use App\Services\Payment\Reconciliation\StaleProcessingPaymentService;
use App\Events\Payment\PaymentVerified;
use App\Events\Payment\PaymentFailed;
use App\Events\Payment\ReconciliationRunCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->paymentService = $this->app->make(PaymentService::class);
    }

    /**
     * Verify that events are dispatched correctly on success and failures.
     */
    public function test_payment_success_and_failure_dispatch_monitoring_events(): void
    {
        Event::fake();

        // 1. Initialize & Finalize success
        $initData = $this->paymentService->initializeWalletFunding($this->user, 100.00, PaymentGateway::RAZORPAY);
        $transaction = PaymentTransaction::findOrFail($initData['transaction_id']);

        $this->paymentService->finalizeSuccessfulFunding($transaction, 'pay_test_ref', 'sig_test', []);
        Event::assertDispatched(PaymentVerified::class);

        // 2. Finalize failure
        $initData2 = $this->paymentService->initializeWalletFunding($this->user, 200.00, PaymentGateway::CASHFREE);
        $transaction2 = PaymentTransaction::findOrFail($initData2['transaction_id']);

        $this->paymentService->finalizeFailedFunding($transaction2, 'Failed verification mock', []);
        Event::assertDispatched(PaymentFailed::class);
    }

    /**
     * Test Stale Processing state recovery service resets transactions back to pending.
     */
    public function test_stale_processing_recovery(): void
    {
        // Create transaction stuck in processing
        $transaction = PaymentTransaction::create([
            'user_id' => $this->user->id,
            'gateway' => PaymentGateway::RAZORPAY,
            'type' => \App\Enums\PaymentTransactionType::WALLET_FUNDING,
            'amount' => 150.00,
            'currency' => 'INR',
            'status' => PaymentTransactionStatus::PROCESSING,
        ]);

        // Shift timestamps back directly via query builder to bypass Eloquent dynamic setting
        \Illuminate\Support\Facades\DB::table('payment_transactions')
            ->where('id', $transaction->id)
            ->update([
                'created_at' => now()->subMinutes(45),
                'updated_at' => now()->subMinutes(45),
            ]);

        $service = $this->app->make(StaleProcessingPaymentService::class);
        $result = $service->recover(30);

        $this->assertEquals(1, $result['total_recovered']);
        $transaction->refresh();
        $this->assertEquals(PaymentTransactionStatus::PENDING, $transaction->status);
    }

    /**
     * Test Abandoned Payment cleanup marks old stale items as abandoned.
     */
    public function test_abandoned_payment_cleanup(): void
    {
        // Create transaction stuck in pending for over 24 hours
        $transaction = PaymentTransaction::create([
            'user_id' => $this->user->id,
            'gateway' => PaymentGateway::CASHFREE,
            'type' => \App\Enums\PaymentTransactionType::WALLET_FUNDING,
            'amount' => 250.00,
            'currency' => 'INR',
            'status' => PaymentTransactionStatus::PENDING,
        ]);

        \Illuminate\Support\Facades\DB::table('payment_transactions')
            ->where('id', $transaction->id)
            ->update([
                'created_at' => now()->subHours(25),
                'updated_at' => now()->subHours(25),
            ]);

        $service = $this->app->make(AbandonedPaymentService::class);
        $result = $service->clean(24);

        $this->assertEquals(1, $result['total_marked_abandoned']);
        $transaction->refresh();
        $this->assertEquals(PaymentTransactionStatus::ABANDONED, $transaction->status);
    }

    /**
     * Test Pending Payment Reconciliation Service auto-resolves paid stale checkouts.
     */
    public function test_pending_payment_reconciliation(): void
    {
        Event::fake();

        // Create transaction stuck in pending (for mock verifyPayment resolving true)
        $transaction = PaymentTransaction::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user->id,
            'gateway' => PaymentGateway::CASHFREE,
            'type' => \App\Enums\PaymentTransactionType::WALLET_FUNDING,
            'amount' => 300.00,
            'currency' => 'INR',
            'status' => PaymentTransactionStatus::PENDING,
            'gateway_order_id' => 'order_mock_' . \Illuminate\Support\Str::random(14),
        ]);

        \Illuminate\Support\Facades\DB::table('payment_transactions')
            ->where('id', $transaction->id)
            ->update([
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(20),
            ]);

        $service = $this->app->make(PendingPaymentReconciliationService::class);
        $result = $service->reconcile(15);

        $this->assertEquals(1, $result['total_resolved']);
        $transaction->refresh();
        $this->assertEquals(PaymentTransactionStatus::SUCCESSFUL, $transaction->status);
    }

    /**
     * Test the Reconcile Command integrates everything and dispatches completed metrics event.
     */
    public function test_reconcile_command_executes_successfully(): void
    {
        Event::fake();

        // Seed stuck/abandoned transactions
        $transaction = PaymentTransaction::create([
            'user_id' => $this->user->id,
            'gateway' => PaymentGateway::RAZORPAY,
            'type' => \App\Enums\PaymentTransactionType::WALLET_FUNDING,
            'amount' => 50.00,
            'currency' => 'INR',
            'status' => PaymentTransactionStatus::PROCESSING,
        ]);

        \Illuminate\Support\Facades\DB::table('payment_transactions')
            ->where('id', $transaction->id)
            ->update([
                'created_at' => now()->subMinutes(40),
                'updated_at' => now()->subMinutes(40),
            ]);

        $this->artisan('payments:reconcile')
             ->assertExitCode(0);

        Event::assertDispatched(ReconciliationRunCompleted::class);
    }
}
