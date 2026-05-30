<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRazorpayWebhookJob;
use App\Services\Payment\PaymentService;
use App\Enums\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming webhooks from Razorpay.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        Log::info("Razorpay webhook received", [
            'has_signature' => !empty($signature)
        ]);

        if (empty($signature)) {
            Log::warning("Razorpay webhook rejected: missing X-Razorpay-Signature header.");
            return response('Signature missing', 400);
        }

        try {
            $driver = $this->paymentService->resolve(PaymentGateway::RAZORPAY);

            // Verify webhook signature cryptographically
            $isValid = $driver->verifyWebhookSignature($payload, $signature);

            if (!$isValid) {
                Log::warning("Razorpay webhook rejected: signature verification failed.");
                return response('Invalid signature', 400);
            }

            $data = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                Log::warning("Razorpay webhook rejected: malformed JSON payload.");
                return response('Malformed payload', 400);
            }

            // Append signature for reference down the pipeline
            $data['signature'] = $signature;

            // Dispatch async job for queue processing
            ProcessRazorpayWebhookJob::dispatch($data);

            Log::info("Razorpay webhook signature verified. Dispatched processing job.", [
                'event' => $data['event'] ?? 'unknown'
            ]);

            // Acknowledge quickly to Razorpay
            return response('Webhook processed', 200);

        } catch (\Exception $e) {
            Log::error("Error handling Razorpay webhook", [
                'error' => $e->getMessage()
            ]);

            return response('Internal error', 500);
        }
    }
}
