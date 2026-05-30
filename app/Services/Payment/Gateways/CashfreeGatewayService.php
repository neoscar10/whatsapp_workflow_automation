<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Exceptions\WalletOperationException;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashfreeGatewayService implements PaymentGatewayInterface
{
    /**
     * Initialize payment on Cashfree.
     *
     * @param PaymentTransaction $transaction
     * @return array Standardized response DTO
     * @throws WalletOperationException
     */
    public function initializePayment(PaymentTransaction $transaction): array
    {
        $appId = config('payment.gateways.cashfree.app_id');
        $secretKey = config('payment.gateways.cashfree.secret_key');
        $environment = config('payment.gateways.cashfree.environment', 'sandbox');
        $timeout = config('payment.gateways.cashfree.timeout', 30);

        // Support test environment and mock credentials gracefully
        if (app()->environment('testing') || empty($appId) || str_starts_with($appId, 'cf_test_mock')) {
            $mockOrderId = 'order_mock_' . Str::random(14);
            $mockSessionId = 'session_mock_' . Str::random(24);

            Log::info("Mock Cashfree payment order initialized", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'cashfree',
                'internal_transaction_id' => $transaction->id,
                'cashfree_order_id' => $mockOrderId,
            ]);

            return [
                'success' => true,
                'gateway' => 'cashfree',
                'gateway_order_id' => $mockOrderId,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'checkout_data' => [
                    'payment_session_id' => $mockSessionId,
                    'order_id' => $mockOrderId,
                    'environment' => 'sandbox',
                ],
            ];
        }

        try {
            // Cashfree SDK env values are 'TEST' (sandbox) or 'PRODUCTION' (live)
            $cfEnv = strtoupper($environment) === 'PRODUCTION' ? 'PRODUCTION' : 'TEST';

            // Initialize custom Guzzle client to control request timeouts
            $httpClient = new \GuzzleHttp\Client([
                'timeout' => $timeout,
                'connect_timeout' => 10,
            ]);

            $cashfree = new \Cashfree\Cashfree(
                $cfEnv,
                $appId,
                $secretKey,
                "", // XPartnerApiKey
                "", // XPartnerMerchantId
                "", // XClientSignature
                false, // XEnableErrorAnalytics
                $httpClient
            );

            // Set explicit API version
            $cashfree->XApiVersion = "2023-08-01";

            // Clean user phone number and fallback if empty (Cashfree requires phone)
            $phone = preg_replace('/[^0-9]/', '', $transaction->user->phone ?? '');
            if (strlen($phone) < 10) {
                $phone = '9999999999';
            }

            // Create Customer Details object
            $customerDetails = new \Cashfree\Model\CustomerDetails([
                'customer_id' => 'cust_' . $transaction->user_id,
                'customer_phone' => $phone,
                'customer_email' => $transaction->user->email,
                'customer_name' => $transaction->user->name,
            ]);

            // Create Order Meta (return url)
            $orderMeta = new \Cashfree\Model\OrderMeta([
                'return_url' => route('wallet.index') . '?order_id={order_id}',
            ]);

            // Create Order Request Payload
            $orderRequest = new \Cashfree\Model\CreateOrderRequest([
                'order_id' => $transaction->id,
                'order_amount' => (float) $transaction->amount,
                'order_currency' => $transaction->currency,
                'customer_details' => $customerDetails,
                'order_meta' => $orderMeta,
                'order_note' => "Wallet Funding - Transaction #{$transaction->id}",
            ]);

            // Call Cashfree API
            $response = $cashfree->PGCreateOrder($orderRequest);
            
            /** @var \Cashfree\Model\OrderEntity $orderEntity */
            $orderEntity = $response[0];

            Log::info("Cashfree payment order initialized successfully", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'cashfree',
                'internal_transaction_id' => $transaction->id,
                'cashfree_order_id' => $orderEntity->getCfOrderId(),
            ]);

            return [
                'success' => true,
                'gateway' => 'cashfree',
                'gateway_order_id' => $orderEntity->getOrderId(),
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'checkout_data' => [
                    'payment_session_id' => $orderEntity->getPaymentSessionId(),
                    'order_id' => $orderEntity->getOrderId(),
                    'environment' => $environment,
                    'return_url' => route('wallet.index') . '?order_id={order_id}',
                ],
            ];

        } catch (\Cashfree\ApiException $e) {
            Log::error("Cashfree API failure", [
                'transaction_id' => $transaction->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody() ? json_decode($e->getResponseBody(), true) : null
            ]);
            throw new WalletOperationException("Cashfree API initialization failed: " . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            Log::error("Cashfree SDK/Exception failure", [
                'transaction_id' => $transaction->id,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            throw new WalletOperationException("Failed to initialize payment gateway order: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Verify payment status using parameters.
     *
     * @param PaymentTransaction $transaction
     * @param array $params
     * @return bool
     */
    public function verifyPayment(PaymentTransaction $transaction, array $params): bool
    {
        $appId = config('payment.gateways.cashfree.app_id');
        $secretKey = config('payment.gateways.cashfree.secret_key');
        $environment = config('payment.gateways.cashfree.environment', 'sandbox');
        $timeout = config('payment.gateways.cashfree.timeout', 30);

        // Support test environment and mock credentials gracefully
        if (app()->environment('testing') || empty($appId) || str_starts_with($appId, 'cf_test_mock')) {
            Log::info("Mock Cashfree payment verification bypass triggered", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'cashfree',
                'internal_transaction_id' => $transaction->id,
            ]);

            // Set mock payment ID and signature
            $transaction->gateway_payment_id = $params['cf_payment_id'] ?? 'pay_mock_' . Str::random(14);
            $transaction->gateway_signature = $params['cf_signature'] ?? 'sig_mock_' . Str::random(24);
            $transaction->save();

            return true;
        }

        try {
            // Cashfree SDK env values are 'TEST' (sandbox) or 'PRODUCTION' (live)
            $cfEnv = strtoupper($environment) === 'PRODUCTION' ? 'PRODUCTION' : 'TEST';

            // Initialize custom Guzzle client to control request timeouts
            $httpClient = new \GuzzleHttp\Client([
                'timeout' => $timeout,
                'connect_timeout' => 10,
            ]);

            $cashfree = new \Cashfree\Cashfree(
                $cfEnv,
                $appId,
                $secretKey,
                "", // XPartnerApiKey
                "", // XPartnerMerchantId
                "", // XClientSignature
                false, // XEnableErrorAnalytics
                $httpClient
            );

            // Set explicit API version
            $cashfree->XApiVersion = "2023-08-01";

            // Call Cashfree API PGFetchOrder using the transaction ID
            $orderResponse = $cashfree->PGFetchOrder($transaction->id);
            /** @var \Cashfree\Model\OrderEntity $orderEntity */
            $orderEntity = $orderResponse[0];

            Log::debug("Cashfree order fetch result", [
                'order_id' => $transaction->id,
                'status' => $orderEntity->getOrderStatus(),
                'amount' => $orderEntity->getOrderAmount(),
                'currency' => $orderEntity->getOrderCurrency()
            ]);

            // Validate order status is PAID
            if ($orderEntity->getOrderStatus() !== 'PAID') {
                Log::warning("Cashfree order is not paid", [
                    'order_id' => $transaction->id,
                    'status' => $orderEntity->getOrderStatus()
                ]);
                return false;
            }

            // Validate amount
            if (abs((float)$orderEntity->getOrderAmount() - (float)$transaction->amount) > 0.01) {
                Log::error("Cashfree order amount mismatch", [
                    'order_id' => $transaction->id,
                    'expected' => $transaction->amount,
                    'received' => $orderEntity->getOrderAmount()
                ]);
                return false;
            }

            // Validate currency
            if (strtoupper($orderEntity->getOrderCurrency()) !== strtoupper($transaction->currency)) {
                Log::error("Cashfree order currency mismatch", [
                    'order_id' => $transaction->id,
                    'expected' => $transaction->currency,
                    'received' => $orderEntity->getOrderCurrency()
                ]);
                return false;
            }

            // Fetch payments to retrieve cf_payment_id
            $paymentsResponse = $cashfree->PGOrderFetchPayments($transaction->id);
            $payments = $paymentsResponse[0] ?? [];

            $successfulPayment = null;
            if (is_array($payments)) {
                foreach ($payments as $payment) {
                    /** @var \Cashfree\Model\PaymentEntity $payment */
                    if ($payment->getPaymentStatus() === 'SUCCESS') {
                        $successfulPayment = $payment;
                        break;
                    }
                }
            }

            if (!$successfulPayment) {
                Log::error("Cashfree order marked PAID but no successful payment found", [
                    'order_id' => $transaction->id
                ]);
                return false;
            }

            // Set the gateway payment ID and signature on the transaction
            $transaction->gateway_payment_id = $successfulPayment->getCfPaymentId();
            $transaction->gateway_signature = $transaction->id;
            $transaction->save();

            Log::info("Cashfree payment verified successfully server-side", [
                'order_id' => $transaction->id,
                'cf_payment_id' => $transaction->gateway_payment_id
            ]);

            return true;

        } catch (\Cashfree\ApiException $e) {
            Log::error("Cashfree API verification failure", [
                'transaction_id' => $transaction->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody() ? json_decode($e->getResponseBody(), true) : null
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Cashfree SDK/Exception verification failure", [
                'transaction_id' => $transaction->id,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Verify incoming webhook signature for Cashfree.
     *
     * @param string $payload Raw request body
     * @param string $signatureHeader Signature header value
     * @param string|null $timestamp Optional timestamp header value
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader, ?string $timestamp = null): bool
    {
        $appId = config('payment.gateways.cashfree.app_id');
        $webhookSecret = config('payment.gateways.cashfree.webhook_secret') ?: config('payment.gateways.cashfree.secret_key');

        if (app()->environment('testing') || empty($appId) || str_starts_with($appId, 'cf_test_mock')) {
            return $signatureHeader === 'valid_mock_webhook_signature';
        }

        if (empty($timestamp)) {
            Log::warning("Cashfree webhook verification failed: timestamp header missing.");
            return false;
        }

        try {
            $body = $timestamp . $payload;
            $genSignature = hash_hmac('sha256', $body, $webhookSecret, true);
            $genSignatureBase64 = base64_encode($genSignature);

            return hash_equals($genSignatureBase64, $signatureHeader);
        } catch (\Exception $e) {
            Log::error("Cashfree webhook signature verification exception", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Normalize incoming webhook payload into a standardized structure.
     *
     * @param string $event Raw event name from provider
     * @param array $payload Raw webhook payload
     * @return array Standardized webhook DTO
     */
    public function normalizeWebhookPayload(string $event, array $payload): array
    {
        $data = $payload['data'] ?? [];
        $orderData = $data['order'] ?? [];
        $paymentData = $data['payment'] ?? [];

        $isSuccess = in_array(strtoupper($event), ['PAYMENT_SUCCESS', 'ORDER_PAID']);
        $isFailed = strtoupper($event) === 'PAYMENT_FAILED';

        return [
            'provider' => 'cashfree',
            'event' => $isSuccess ? 'payment_success' : ($isFailed ? 'payment_failed' : $event),
            'gateway_order_id' => $orderData['order_id'] ?? $payload['order_id'] ?? '',
            'payment_reference' => (string) ($paymentData['cf_payment_id'] ?? ''),
            'amount' => (float) ($paymentData['payment_amount'] ?? $orderData['order_amount'] ?? 0.0),
            'currency' => $paymentData['payment_currency'] ?? $orderData['order_currency'] ?? 'INR',
            'status' => $isSuccess ? 'successful' : ($isFailed ? 'failed' : 'pending'),
            'failure_reason' => $paymentData['payment_message'] ?? null,
            'raw' => $payload,
        ];
    }
}
