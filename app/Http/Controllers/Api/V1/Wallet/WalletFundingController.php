<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Wallet\FundWalletRequest;
use App\Http\Requests\Api\V1\Wallet\VerifyFundingRequest;
use App\Http\Resources\Api\V1\Payment\PaymentTransactionResource;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WalletFundingController extends Controller
{
    use RespondsWithApiResponse;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initialize a wallet funding session on the gateway.
     *
     * @param FundWalletRequest $request
     * @return JsonResponse
     */
    public function initialize(FundWalletRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $amount = $request->input('amount');
            $gateway = $request->input('gateway'); // defaults to payment.default

            $payload = $this->paymentService->initializeWalletFunding($user, $amount, $gateway);

            return $this->successResponse(
                $payload,
                'Payment initialization successful.',
                201
            );
        } catch (Exception $e) {
            Log::error("Wallet funding initialization endpoint error", [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Unable to initialize payment. Please try again.',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Verify payment status and credit the wallet.
     *
     * @param VerifyFundingRequest $request
     * @param string $transactionId
     * @return JsonResponse
     */
    public function verify(VerifyFundingRequest $request, string $transactionId): JsonResponse
    {
        try {
            $params = $request->only([
                'razorpay_payment_id', 
                'razorpay_order_id', 
                'razorpay_signature',
                'cf_payment_id',
                'cf_signature'
            ]);
            
            $transaction = $this->paymentService->verifyWalletFunding($transactionId, $params);

            return $this->successResponse(
                new PaymentTransactionResource($transaction),
                'Payment verified and wallet credited successfully.'
            );
        } catch (\App\Exceptions\PaymentVerificationFailedException $e) {
            return $this->errorResponse(
                'Payment signature verification failed. Secure rejection.',
                [],
                400
            );
        } catch (\App\Exceptions\InvalidPaymentStateException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                [],
                422
            );
        } catch (Exception $e) {
            Log::error("Wallet funding verification endpoint error", [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'An error occurred during verification. Please contact support.',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}

