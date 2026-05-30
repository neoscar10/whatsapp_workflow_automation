<?php

namespace Tests\Feature\Wallet;

use App\Jobs\ProcessRazorpayWebhookJob;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use App\Enums\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WalletWebhookTest extends TestCase
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

    public function test_webhook_requires_signature(): void
    {
        $response = $this->postJson(route('api.v1.webhooks.razorpay'), [
            'event' => 'payment.captured'
        ]);

        $response->assertStatus(400);
        $response->assertSee('Signature missing');
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'invalid_signature'
        ])->postJson(route('api.v1.webhooks.razorpay'), [
            'event' => 'payment.captured'
        ]);

        $response->assertStatus(400);
        $response->assertSee('Invalid signature');
    }

    public function test_webhook_dispatches_queue_job_with_valid_signature(): void
    {
        Queue::fake();

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'valid_mock_webhook_signature'
        ])->postJson(route('api.v1.webhooks.razorpay'), [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_mock123',
                        'amount' => 50000, // in paisa
                        'currency' => 'INR',
                        'order_id' => 'order_mock123'
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertSee('Webhook processed');

        Queue::assertPushed(ProcessRazorpayWebhookJob::class);
    }

    public function test_job_processes_successful_funding(): void
    {
        // First initialize a payment transaction
        $initData = $this->paymentService->initializeWalletFunding($this->user, 100.00, PaymentGateway::RAZORPAY);
        $transactionId = $initData['transaction_id'];
        $orderId = $initData['gateway_order_id'];

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_captured123',
                        'amount' => 10000, // 100 INR in paisa
                        'currency' => 'INR',
                        'order_id' => $orderId
                    ]
                ]
            ]
        ];

        // Execute the job synchronously
        $job = new ProcessRazorpayWebhookJob($payload);
        $job->handle($this->paymentService);

        // Verify database states
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'gateway_payment_id' => 'pay_captured123'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('100.0000', $wallet->balance);
    }

    public function test_job_is_idempotent_against_duplicate_webhooks(): void
    {
        $initData = $this->paymentService->initializeWalletFunding($this->user, 100.00, PaymentGateway::RAZORPAY);
        $transactionId = $initData['transaction_id'];
        $orderId = $initData['gateway_order_id'];

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_captured123',
                        'amount' => 10000,
                        'currency' => 'INR',
                        'order_id' => $orderId
                    ]
                ]
            ]
        ];

        // Process webhook first time
        $job1 = new ProcessRazorpayWebhookJob($payload);
        $job1->handle($this->paymentService);

        // Process webhook second time (duplicate event replay)
        $job2 = new ProcessRazorpayWebhookJob($payload);
        $job2->handle($this->paymentService);

        // Check wallet balance is only credited once
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('100.0000', $wallet->balance);
    }

    public function test_job_processes_failed_funding(): void
    {
        $initData = $this->paymentService->initializeWalletFunding($this->user, 150.00, PaymentGateway::RAZORPAY);
        $transactionId = $initData['transaction_id'];
        $orderId = $initData['gateway_order_id'];

        $payload = [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_failed123',
                        'amount' => 15000,
                        'currency' => 'INR',
                        'order_id' => $orderId,
                        'error_description' => 'User aborted transaction'
                    ]
                ]
            ]
        ];

        $job = new ProcessRazorpayWebhookJob($payload);
        $job->handle($this->paymentService);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'failed'
        ]);

        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $wallet->refresh();
        $this->assertEquals('0.0000', $wallet->balance);
    }
}
