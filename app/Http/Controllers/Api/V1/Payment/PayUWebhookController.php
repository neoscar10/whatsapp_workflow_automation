<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayUWebhookJob;
use App\Services\Payment\PaymentService;
use App\Enums\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayUWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming webhooks / POST callbacks from PayU.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $allParams = $request->all();

        Log::info("PayU webhook/callback received", [
            'has_params' => !empty($allParams),
            'has_hash' => isset($allParams['hash']),
            'txnid' => $allParams['txnid'] ?? null,
        ]);

        if (empty($allParams) || empty($allParams['hash'])) {
            Log::warning("PayU webhook rejected: missing required params or hash.");
            return response('Params or hash missing', 400);
        }

        try {
            $driver = $this->paymentService->resolve(PaymentGateway::PAYU);

            $signatureHeader = $allParams['hash'] ?? $request->header('x-payu-signature') ?? '';

            // Verify webhook signature or hash
            $isValid = $driver->verifyWebhookSignature($payload, $signatureHeader);

            if (!$isValid) {
                Log::warning("PayU webhook rejected: hash verification failed.");
                return response('Invalid hash', 400);
            }

            // Dispatch async job for queue processing
            ProcessPayUWebhookJob::dispatch($allParams);

            Log::info("PayU webhook verified successfully. Dispatched processing job.");

            return response('Webhook processed', 200);

        } catch (\Exception $e) {
            Log::error("Error handling PayU webhook", [
                'error' => $e->getMessage()
            ]);

            return response('Internal error', 500);
        }
    }
}
