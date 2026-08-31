<?php

namespace App\Jobs;

use App\Services\Payment\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayUWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout;

    /**
     * The webhook event payload.
     *
     * @var array
     */
    protected array $payload;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->tries = config('payment.webhook.tries', 3);
        $this->timeout = config('payment.webhook.timeout', 60);
        $this->queue = config('payment.webhook.queue', 'default');
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff(): array
    {
        return config('payment.webhook.backoff', [10, 30, 60]);
    }

    /**
     * Execute the job.
     *
     * @param PaymentService $paymentService
     * @return void
     */
    public function handle(PaymentService $paymentService): void
    {
        $status = $this->payload['status'] ?? '';
        $event = in_array(strtolower($status), ['success', 'captured']) ? 'payment_success' : 'payment_failed';

        Log::info("ProcessPayUWebhookJob started processing", [
            'event' => $event,
            'txnid' => $this->payload['txnid'] ?? null,
            'job_id' => $this->job?->getJobId()
        ]);

        try {
            $paymentService->handleWebhookEvent($event, $this->payload, 'payu');
        } catch (\Exception $e) {
            Log::error("ProcessPayUWebhookJob failed during handle", [
                'event' => $event,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);

            throw $e;
        }
    }
}
