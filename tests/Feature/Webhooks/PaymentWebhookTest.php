<?php

namespace Tests\Feature\Webhooks;

use App\Enums\PaymentGateway;
use App\Enums\PaymentTransactionStatus;
use App\Enums\WalletTransactionCategory;
use App\Jobs\ProcessCashfreeWebhookJob;
use App\Jobs\ProcessRazorpayWebhookJob;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PaymentService $paymentService;
    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->paymentService = $this->app->make(PaymentService::class);
        $this->walletService = $this->app->make(WalletService::class);
    }

    /**
     * Test Razorpay Webhook signature verification failure.
     */
    public function test_razorpay_webhook_fails_with_invalid_signature(): void
    {
        Bus::fake();

        $response = $this->postJson(route('api.v1.webhooks.razorpay'), [
            'event' => 'payment.captured',
        ], [
            'X-Razorpay-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
        Bus::assertNotDispatched(ProcessRazorpayWebhookJob::class);
    }

    /**
     * Test Razorpay Webhook signature verification success.
     */
    public function test_razorpay_webhook_dispatches_job_with_valid_signature(): void
    {
        Bus::fake();

        $response = $this->postJson(route('api.v1.webhooks.razorpay'), [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_mock123',
                        'order_id' => 'order_mock123',
                        'amount' => 10000,
                        'currency' => 'INR'
                    ]
                ]
            ]
        ], [
            'X-Razorpay-Signature' => 'valid_mock_webhook_signature'
        ]);

        $response->assertStatus(200);
        Bus::assertDispatched(ProcessRazorpayWebhookJob::class);
    }

    /**
     * Test Cashfree Webhook signature verification failure.
     */
    public function test_cashfree_webhook_fails_with_invalid_signature(): void
    {
        Bus::fake();

        $response = $this->postJson(route('api.v1.webhooks.cashfree'), [
            'event' => 'PAYMENT_SUCCESS',
        ], [
            'x-webhook-signature' => 'invalid_signature',
            'x-webhook-timestamp' => '1633614002000'
        ]);

        $response->assertStatus(400);
        Bus::assertNotDispatched(ProcessCashfreeWebhookJob::class);
    }

    /**
     * Test Cashfree Webhook signature verification success.
     */
    public function test_cashfree_webhook_dispatches_job_with_valid_signature(): void
    {
        Bus::fake();

        $response = $this->postJson(route('api.v1.webhooks.cashfree'), [
            'event' => 'PAYMENT_SUCCESS',
            'data' => [
                'order' => [
                    'order_id' => 'order_mock123',
                    'order_amount' => 100.00,
                    'order_currency' => 'INR'
                ],
                'payment' => [
                    'cf_payment_id' => '987654',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 100.00,
                    'payment_currency' => 'INR'
                ]
            ]
        ], [
            'x-webhook-signature' => 'valid_mock_webhook_signature',
            'x-webhook-timestamp' => '1633614002000'
        ]);

        $response->assertStatus(200);
        Bus::assertDispatched(ProcessCashfreeWebhookJob::class);
    }

    /**
     * Test ProcessCashfreeWebhookJob processes PAYMENT_SUCCESS correctly.
     */
    public function test_process_cashfree_webhook_job_finalizes_success(): void
    {
        // 1. Initialize a cashfree funding transaction
        $initData = $this->paymentService->initializeWalletFunding($this->user, 150.00, PaymentGateway::CASHFREE);
        $transactionId = $initData['transaction_id'];

        $payload = [
            'event' => 'PAYMENT_SUCCESS',
            'data' => [
                'order' => [
                    'order_id' => $transactionId,
                    'order_amount' => 150.00,
                    'order_currency' => 'INR'
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_987654',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 150.00,
                    'payment_currency' => 'INR'
                ]
            ]
        ];

        // 2. Dispatch and execute the job manually
        $job = new ProcessCashfreeWebhookJob($payload);
        $job->handle($this->paymentService);

        // 3. Assert payment transaction is updated to successful
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => 'cf_pay_987654'
        ]);

        // 4. Assert user's wallet is credited
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('150.0000', $wallet->balance);

        // 5. Assert wallet transaction is recorded
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => '150.0000',
            'status' => 'successful',
            'category' => 'funding'
        ]);
    }

    /**
     * Test Cashfree webhook processing is idempotent.
     */
    public function test_process_cashfree_webhook_is_idempotent(): void
    {
        $initData = $this->paymentService->initializeWalletFunding($this->user, 200.00, PaymentGateway::CASHFREE);
        $transactionId = $initData['transaction_id'];

        $payload = [
            'event' => 'PAYMENT_SUCCESS',
            'data' => [
                'order' => [
                    'order_id' => $transactionId,
                    'order_amount' => 200.00,
                    'order_currency' => 'INR'
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_987654',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 200.00,
                    'payment_currency' => 'INR'
                ]
            ]
        ];

        // Run webhook processing first time
        $job = new ProcessCashfreeWebhookJob($payload);
        $job->handle($this->paymentService);

        // Run webhook processing second time (simulated webhook retry)
        $job->handle($this->paymentService);

        // Check balance is only credited once
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('200.0000', $wallet->balance);
    }
}
