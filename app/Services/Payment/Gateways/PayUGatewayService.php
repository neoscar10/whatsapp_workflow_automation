<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Exceptions\WalletOperationException;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayUGatewayService implements PaymentGatewayInterface
{
    /**
     * Initialize payment on PayU.in.
     *
     * @param PaymentTransaction $transaction
     * @return array Standardized response DTO
     * @throws WalletOperationException
     */
    public function initializePayment(PaymentTransaction $transaction): array
    {
        $key = config('payment.gateways.payu.key');
        $salt = config('payment.gateways.payu.salt');
        $environment = config('payment.gateways.payu.environment', 'test');
        $timeout = config('payment.gateways.payu.timeout', 30);

        // Support test environment and mock credentials gracefully
        if (app()->environment('testing') || empty($key) || str_starts_with($key, 'payu_test_mock')) {
            $mockOrderId = $transaction->id;
            $mockHash = 'hash_mock_' . Str::random(24);

            Log::info("Mock PayU payment order initialized", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'payu',
                'internal_transaction_id' => $transaction->id,
            ]);

            return [
                'success' => true,
                'gateway' => 'payu',
                'gateway_order_id' => $mockOrderId,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'checkout_data' => [
                    'action_url' => 'https://test.payu.in/_payment',
                    'params' => [
                        'key' => $key ?: 'iXlXRj',
                        'txnid' => $transaction->id,
                        'amount' => number_format((float) $transaction->amount, 2, '.', ''),
                        'productinfo' => "Wallet Funding - Transaction #{$transaction->id}",
                        'firstname' => $transaction->user->name ?? 'User',
                        'email' => $transaction->user->email ?? 'user@example.com',
                        'phone' => '9999999999',
                        'surl' => route('wallet.index'),
                        'furl' => route('wallet.index'),
                        'hash' => $mockHash,
                    ]
                ],
            ];
        }

        try {
            $amountFormatted = number_format((float) $transaction->amount, 2, '.', '');
            $productInfo = "Wallet Funding - Transaction #{$transaction->id}";
            $firstName = $transaction->user->name ?? 'Customer';
            $email = $transaction->user->email ?? 'customer@example.com';
            
            $phone = preg_replace('/[^0-9]/', '', $transaction->user->phone ?? '');
            if (strlen($phone) < 10) {
                $phone = '9999999999';
            }

            $surl = route('wallet.index') . '?status=success&txnid=' . $transaction->id;
            $furl = route('wallet.index') . '?status=failure&txnid=' . $transaction->id;

            $hash = $this->generateRequestHash(
                $key,
                $transaction->id,
                $amountFormatted,
                $productInfo,
                $firstName,
                $email,
                $salt
            );

            $actionUrl = strtolower($environment) === 'production'
                ? 'https://secure.payu.in/_payment'
                : 'https://test.payu.in/_payment';

            Log::info("PayU payment order initialized successfully", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'payu',
                'internal_transaction_id' => $transaction->id,
            ]);

            return [
                'success' => true,
                'gateway' => 'payu',
                'gateway_order_id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'checkout_data' => [
                    'action_url' => $actionUrl,
                    'params' => [
                        'key' => $key,
                        'txnid' => $transaction->id,
                        'amount' => $amountFormatted,
                        'productinfo' => $productInfo,
                        'firstname' => $firstName,
                        'email' => $email,
                        'phone' => $phone,
                        'surl' => $surl,
                        'furl' => $furl,
                        'hash' => $hash,
                    ]
                ],
            ];
        } catch (\Exception $e) {
            Log::error("PayU initialization failure", [
                'transaction_id' => $transaction->id,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            throw new WalletOperationException("Failed to initialize PayU payment order: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Verify payment status using server-side PayU API or signature response.
     *
     * @param PaymentTransaction $transaction
     * @param array $params
     * @return bool
     */
    public function verifyPayment(PaymentTransaction $transaction, array $params): bool
    {
        $key = config('payment.gateways.payu.key');
        $salt = config('payment.gateways.payu.salt');
        $environment = config('payment.gateways.payu.environment', 'test');
        $timeout = config('payment.gateways.payu.timeout', 30);

        // Support test environment and mock credentials gracefully
        if (app()->environment('testing') || empty($key) || str_starts_with($key, 'payu_test_mock')) {
            Log::info("Mock PayU payment verification bypass triggered", [
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'gateway' => 'payu',
                'internal_transaction_id' => $transaction->id,
            ]);

            $transaction->gateway_payment_id = $params['mihpayid'] ?? $params['payuMoneyId'] ?? 'payu_mock_' . Str::random(14);
            $transaction->gateway_signature = $params['hash'] ?? 'hash_mock_' . Str::random(24);
            $transaction->save();

            return true;
        }

        try {
            // First check if reverse hash verification is supplied in $params
            if (!empty($params['hash']) && !empty($params['status'])) {
                $isValidHash = $this->verifyResponseHash($params, $salt, $key);
                if ($isValidHash && strtolower($params['status']) === 'success') {
                    $transaction->gateway_payment_id = $params['mihpayid'] ?? $params['payuMoneyId'] ?? $transaction->id;
                    $transaction->gateway_signature = $params['hash'];
                    $transaction->save();

                    Log::info("PayU payment verified via response hash check", [
                        'transaction_id' => $transaction->id,
                        'mihpayid' => $transaction->gateway_payment_id
                    ]);

                    return true;
                }
            }

            // Perform server-side API verification via PayU verify_payment web service
            $apiEndpoint = strtolower($environment) === 'production'
                ? 'https://info.payu.in/merchant/postservice?form=2'
                : 'https://test.payu.in/merchant/postservice?form=2';

            $command = 'verify_payment';
            $var1 = $transaction->id;
            $hashSequence = "{$key}|{$command}|{$var1}|{$salt}";
            $hash = strtolower(hash('sha512', $hashSequence));

            $response = Http::timeout($timeout)->asForm()->post($apiEndpoint, [
                'key' => $key,
                'command' => $command,
                'var1' => $var1,
                'hash' => $hash,
            ]);

            if (!$response->successful()) {
                Log::error("PayU verify_payment API request failed", [
                    'transaction_id' => $transaction->id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $data = $response->json();

            Log::debug("PayU verify_payment API response", [
                'transaction_id' => $transaction->id,
                'data' => $data
            ]);

            if (isset($data['status']) && (int)$data['status'] === 1 && isset($data['transaction_details'][$transaction->id])) {
                $details = $data['transaction_details'][$transaction->id];
                $payuStatus = strtolower($details['status'] ?? '');
                $unmappedStatus = strtolower($details['unmappedstatus'] ?? '');

                if ($payuStatus === 'success' || $unmappedStatus === 'captured') {
                    $receivedAmount = (float) ($details['amount'] ?? 0.0);
                    if (abs($receivedAmount - (float) $transaction->amount) <= 0.01) {
                        $transaction->gateway_payment_id = (string) ($details['mihpayid'] ?? $params['mihpayid'] ?? $transaction->id);
                        $transaction->gateway_signature = $hash;
                        $transaction->save();

                        Log::info("PayU payment verified successfully via server API", [
                            'transaction_id' => $transaction->id,
                            'mihpayid' => $transaction->gateway_payment_id
                        ]);

                        return true;
                    } else {
                        Log::error("PayU verification amount mismatch", [
                            'transaction_id' => $transaction->id,
                            'expected' => $transaction->amount,
                            'received' => $receivedAmount
                        ]);
                    }
                }
            }

            Log::warning("PayU server verification returned non-success state", [
                'transaction_id' => $transaction->id,
                'response' => $data
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("PayU verification exception", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verify incoming webhook signature or hash for PayU.
     *
     * @param string $payload Raw request body
     * @param string $signatureHeader Signature header value
     * @param string|null $timestamp Optional timestamp header value
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader, ?string $timestamp = null): bool
    {
        $key = config('payment.gateways.payu.key');
        $salt = config('payment.gateways.payu.salt');

        if (app()->environment('testing') || empty($key) || str_starts_with($key, 'payu_test_mock')) {
            return $signatureHeader === 'valid_mock_webhook_signature';
        }

        $data = json_decode($payload, true);
        if (!$data || !is_array($data)) {
            parse_str($payload, $data);
        }

        if (empty($data) || empty($data['hash'])) {
            return false;
        }

        return $this->verifyResponseHash($data, $salt, $key);
    }

    /**
     * Normalize incoming webhook/callback payload into a standardized structure.
     *
     * @param string $event Raw event name from provider
     * @param array $payload Raw webhook payload
     * @return array Standardized webhook DTO
     */
    public function normalizeWebhookPayload(string $event, array $payload): array
    {
        $status = strtolower($payload['status'] ?? '');
        $unmappedStatus = strtolower($payload['unmappedstatus'] ?? '');

        $isSuccess = in_array($status, ['success', 'captured']) || in_array($unmappedStatus, ['captured', 'success']);
        $isFailed = in_array($status, ['failure', 'failed']) || in_array($unmappedStatus, ['usercancelled', 'bounced', 'failed']);

        return [
            'provider' => 'payu',
            'event' => $isSuccess ? 'payment_success' : ($isFailed ? 'payment_failed' : $event),
            'gateway_order_id' => $payload['txnid'] ?? $payload['order_id'] ?? '',
            'payment_reference' => (string) ($payload['mihpayid'] ?? $payload['payuMoneyId'] ?? ''),
            'amount' => (float) ($payload['amount'] ?? 0.0),
            'currency' => $payload['currency'] ?? 'INR',
            'status' => $isSuccess ? 'successful' : ($isFailed ? 'failed' : 'pending'),
            'failure_reason' => $payload['error_Message'] ?? $payload['field9'] ?? $payload['msg'] ?? null,
            'raw' => $payload,
        ];
    }

    /**
     * Generate PayU outbound request SHA512 hash.
     * Formula: sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
     */
    public function generateRequestHash(
        string $key,
        string $txnid,
        string $amount,
        string $productinfo,
        string $firstname,
        string $email,
        string $salt,
        array $udfs = []
    ): string {
        $udf1 = $udfs['udf1'] ?? '';
        $udf2 = $udfs['udf2'] ?? '';
        $udf3 = $udfs['udf3'] ?? '';
        $udf4 = $udfs['udf4'] ?? '';
        $udf5 = $udfs['udf5'] ?? '';

        $hashSequence = "{$key}|{$txnid}|{$amount}|{$productinfo}|{$firstname}|{$email}|{$udf1}|{$udf2}|{$udf3}|{$udf4}|{$udf5}||||||{$salt}";
        return strtolower(hash('sha512', $hashSequence));
    }

    /**
     * Verify PayU inbound response SHA512 reverse hash.
     * Formula: sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key)
     * If additionalCharges present: sha512(additionalCharges|SALT|status|...)
     */
    public function verifyResponseHash(array $params, string $salt, string $key): bool
    {
        if (empty($params['hash'])) {
            return false;
        }

        $receivedHash = strtolower($params['hash']);
        $status = $params['status'] ?? '';
        $txnid = $params['txnid'] ?? '';
        $amount = $params['amount'] ?? '';
        $productinfo = $params['productinfo'] ?? '';
        $firstname = $params['firstname'] ?? '';
        $email = $params['email'] ?? '';
        $udf1 = $params['udf1'] ?? '';
        $udf2 = $params['udf2'] ?? '';
        $udf3 = $params['udf3'] ?? '';
        $udf4 = $params['udf4'] ?? '';
        $udf5 = $params['udf5'] ?? '';
        $additionalCharges = $params['additionalCharges'] ?? null;

        if ($additionalCharges) {
            $hashSequence = "{$additionalCharges}|{$salt}|{$status}||||||{$udf5}|{$udf4}|{$udf3}|{$udf2}|{$udf1}|{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
        } else {
            $hashSequence = "{$salt}|{$status}||||||{$udf5}|{$udf4}|{$udf3}|{$udf2}|{$udf1}|{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
        }

        $calculatedHash = strtolower(hash('sha512', $hashSequence));
        return hash_equals($calculatedHash, $receivedHash);
    }
}
