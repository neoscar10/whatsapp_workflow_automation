<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCashfreeWebhookJob;
use App\Services\Payment\PaymentService;
use App\Enums\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CashfreeWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming webhooks from Cashfree.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $signature = $request->header('x-webhook-signature');
        $timestamp = $request->header('x-webhook-timestamp');
        $payload = $request->getContent();

        Log::info("Cashfree webhook received", [
            'has_signature' => !empty($signature),
            'has_timestamp' => !empty($timestamp)
        ]);

        if (empty($signature) || empty($timestamp)) {
            Log::warning("Cashfree webhook rejected: missing signature or timestamp headers.");
            return response('Headers missing', 400);
        }

        try {
            $driver = $this->paymentService->resolve(PaymentGateway::CASHFREE);

            // Verify webhook signature cryptographically
            $isValid = $driver->verifyWebhookSignature($payload, $signature, $timestamp);

            if (!$isValid) {
                Log::warning("Cashfree webhook rejected: signature verification failed.");
                return response('Invalid signature', 400);
            }

            $data = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                Log::warning("Cashfree webhook rejected: malformed JSON payload.");
                return response('Malformed payload', 400);
            }

            // Append signature and timestamp for reference down the pipeline
            $data['signature'] = $signature;
            $data['timestamp'] = $timestamp;

            // Dispatch async job for queue processing
            ProcessCashfreeWebhookJob::dispatch($data);

            Log::info("Cashfree webhook signature verified. Dispatched processing job.");

            // Acknowledge quickly to Cashfree
            return response('Webhook processed', 200);

        } catch (\Exception $e) {
            Log::error("Error handling Cashfree webhook", [
                'error' => $e->getMessage()
            ]);

            return response('Internal error', 500);
        }
    }
}
