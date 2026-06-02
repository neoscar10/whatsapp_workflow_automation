<?php

namespace App\Services\Payment;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use App\Enums\WalletTransactionCategory;
use App\Exceptions\DuplicatePaymentException;
use App\Exceptions\InvalidPaymentGatewayException;
use App\Exceptions\InvalidPaymentStateException;
use App\Exceptions\PaymentVerificationFailedException;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\Payment\Gateways\CashfreeGatewayService;
use App\Services\Payment\Gateways\RazorpayGatewayService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Manager;

class PaymentService extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('payment.default', 'razorpay');
    }

    /**
     * Create Razorpay driver.
     */
    public function createRazorpayDriver(): PaymentGatewayInterface
    {
        return new RazorpayGatewayService();
    }

    /**
     * Create Cashfree driver.
     */
    public function createCashfreeDriver(): PaymentGatewayInterface
    {
        return new CashfreeGatewayService();
    }

    /**
     * Resolve a gateway driver, supporting PaymentGateway enum or string.
     *
     * @param string|PaymentGateway|null $gateway
     * @return PaymentGatewayInterface
     * @throws InvalidPaymentGatewayException
     */
    public function resolve(string|PaymentGateway $gateway = null): PaymentGatewayInterface
    {
        $driverName = $gateway instanceof PaymentGateway ? $gateway->value : $gateway;

        try {
            return $this->driver($driverName);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidPaymentGatewayException("Payment gateway [{$driverName}] is not supported.");
        }
    }

    /**
     * Initialize wallet funding request on the chosen gateway.
     *
     * @param User $user
     * @param float|string $amount
     * @param string|PaymentGateway|null $gateway
     * @return array Standardized response payload
     */
    public function initializeWalletFunding(User $user, float|string $amount, string|PaymentGateway $gateway = null): array
    {
        $gatewayEnum = $gateway instanceof PaymentGateway 
            ? $gateway 
            : ($gateway ? PaymentGateway::from($gateway) : PaymentGateway::from($this->getDefaultDriver()));
        
        $driver = $this->resolve($gatewayEnum);

        return DB::transaction(function () use ($user, $amount, $gatewayEnum, $driver) {
            // Create pending payment transaction
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'gateway' => $gatewayEnum,
                'type' => PaymentTransactionType::WALLET_FUNDING,
                'amount' => $amount,
                'currency' => config('payment.currency', 'INR'),
                'status' => PaymentTransactionStatus::PENDING,
            ]);

            try {
                // Call the driver initialization
                $initResponse = $driver->initializePayment($paymentTransaction);

                // Update the transaction with order details and payload
                $paymentTransaction->update([
                    'gateway_order_id' => $initResponse['gateway_order_id'],
                    'payload' => $initResponse,
                ]);

                return [
                    'transaction_id' => $paymentTransaction->id,
                    'gateway' => $gatewayEnum->value,
                    'gateway_order_id' => $initResponse['gateway_order_id'],
                    'amount' => $paymentTransaction->amount,
                    'currency' => $paymentTransaction->currency,
                    'checkout_data' => $initResponse['checkout_data'] ?? [],
                ];
            } catch (\Exception $e) {
                Log::error("Failed to initialize wallet funding payment order", [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'gateway' => $gatewayEnum->value,
                    'error' => $e->getMessage()
                ]);

                $paymentTransaction->update([
                    'status' => PaymentTransactionStatus::FAILED,
                    'payload' => ['error' => $e->getMessage()]
                ]);

                throw $e;
            }
        });
    }

    /**
     * Verify payment transaction status and credit the wallet.
     *
     * @param string $transactionId
     * @param array $verificationParams
     * @return PaymentTransaction
     * @throws DuplicatePaymentException
     * @throws InvalidPaymentStateException
     * @throws PaymentVerificationFailedException
     */
    public function verifyWalletFunding(string $transactionId, array $verificationParams): PaymentTransaction
    {
        Log::debug("[Wallet Verification Step 1: Start]", [
            'transaction_id' => $transactionId,
            'params' => $verificationParams
        ]);

        return DB::transaction(function () use ($transactionId, $verificationParams) {
            // Pessimistic row locking of the payment transaction
            $paymentTransaction = PaymentTransaction::where('id', $transactionId)->lockForUpdate()->firstOrFail();

            Log::debug("[Wallet Verification Step 2: Locked Row]", [
                'id' => $paymentTransaction->id,
                'status' => $paymentTransaction->status->value,
                'gateway_order_id' => $paymentTransaction->gateway_order_id,
            ]);

            // Check if already processed (Idempotency protection)
            if ($paymentTransaction->status === PaymentTransactionStatus::SUCCESSFUL) {
                Log::info("Idempotency match: Duplicate verification skipped for transaction ID: {$transactionId}");
                return $paymentTransaction;
            }

            if ($paymentTransaction->status === PaymentTransactionStatus::FAILED) {
                Log::warning("[Wallet Verification Step 2.5: Blocked Failed State]", ['id' => $transactionId]);
                throw new InvalidPaymentStateException("Cannot verify a failed payment transaction.");
            }

            $driver = $this->resolve($paymentTransaction->gateway);

            // Perform server-side signature verification
            Log::debug("[Wallet Verification Step 3: Triggering Driver verifyPayment]", [
                'gateway' => $paymentTransaction->gateway->value
            ]);
            $verified = $driver->verifyPayment($paymentTransaction, $verificationParams);

            if (!$verified) {
                Log::warning("Payment verification failed for transaction ID: {$transactionId}", [
                    'gateway' => $paymentTransaction->gateway->value,
                    'params' => $verificationParams,
                    'gateway_order_id' => $paymentTransaction->gateway_order_id,
                ]);

                $this->finalizeFailedFunding($paymentTransaction, "Payment verification failed", $verificationParams);

                throw new PaymentVerificationFailedException();
            }

            Log::debug("[Wallet Verification Step 4: Driver Verified. Finalizing success...]");

            $gatewayPaymentId = '';
            $gatewaySignature = '';

            if ($paymentTransaction->gateway === PaymentGateway::RAZORPAY) {
                $gatewayPaymentId = $verificationParams['razorpay_payment_id'] ?? '';
                $gatewaySignature = $verificationParams['razorpay_signature'] ?? '';
            } elseif ($paymentTransaction->gateway === PaymentGateway::CASHFREE) {
                $gatewayPaymentId = $paymentTransaction->gateway_payment_id ?? $verificationParams['cf_payment_id'] ?? '';
                $gatewaySignature = $paymentTransaction->gateway_signature ?? $verificationParams['cf_signature'] ?? 'api_verified';
            }

            return $this->finalizeSuccessfulFunding(
                $paymentTransaction,
                $gatewayPaymentId,
                $gatewaySignature,
                $verificationParams
            );
        });
    }

    /**
     * Centralized funding completion method.
     *
     * @param PaymentTransaction $transaction
     * @param string $gatewayPaymentId
     * @param string $gatewaySignature
     * @param array $payload
     * @return PaymentTransaction
     */
    public function finalizeSuccessfulFunding(
        PaymentTransaction $transaction,
        string $gatewayPaymentId,
        string $gatewaySignature,
        array $payload
    ): PaymentTransaction {
        Log::debug("[Wallet Finalization Step 1: Start]", [
            'id' => $transaction->id,
            'gateway_payment_id' => $gatewayPaymentId,
        ]);

        return DB::transaction(function () use ($transaction, $gatewayPaymentId, $gatewaySignature, $payload) {
            // Lock payment transaction row
            $transaction = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            // Double crediting protection (Idempotency check)
            if ($transaction->status === PaymentTransactionStatus::SUCCESSFUL) {
                Log::info("Idempotency match: Transaction {$transaction->id} already processed successful.");
                return $transaction;
            }

            // Credit the user's wallet
            $walletService = app(WalletService::class);
            Log::debug("[Wallet Finalization Step 2: Resolving Wallet]", ['user_id' => $transaction->user_id]);
            $wallet = $walletService->getOrCreateWallet($transaction->user);

            // Credit operation updates balance and creates wallet ledger entry internally
            Log::debug("[Wallet Finalization Step 3: Crediting Wallet Account]", [
                'wallet_id' => $wallet->id,
                'amount' => $transaction->amount
            ]);
            $walletService->credit(
                wallet: $wallet,
                amount: $transaction->amount,
                category: WalletTransactionCategory::FUNDING,
                description: "Wallet funding via " . ucfirst($transaction->gateway->value),
                providerReference: $gatewayPaymentId,
                metadata: array_merge($payload, ['gateway' => $transaction->gateway->value]),
                createdBy: $transaction->user
            );

            // Mark payment transaction successful
            $transaction->update([
                'status' => PaymentTransactionStatus::SUCCESSFUL,
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_signature' => $gatewaySignature,
                'completed_at' => now(),
                'payload' => array_merge($transaction->payload ?? [], [
                    'finalized_success' => $payload,
                ]),
            ]);

            // Save purchased package details to company_packages table
            $company = $transaction->user->company ?? null;
            if ($company) {
                $package = \App\Models\FundingPackage::where('is_active', true)
                    ->where('amount', $transaction->amount)
                    ->first();

                if (!$package) {
                    $package = \App\Models\FundingPackage::where('amount', $transaction->amount)->first();
                }

                if ($package) {
                    \App\Models\CompanyPackage::create([
                        'company_id' => $company->id,
                        'payment_transaction_id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'remaining_balance' => $transaction->amount,
                        'text_rate' => $package->text_rate,
                        'template_utility_rate' => $package->template_utility_rate,
                        'template_auth_rate' => $package->template_auth_rate,
                        'template_marketing_rate' => $package->template_marketing_rate,
                        'automation_rate' => $package->automation_rate,
                        'status' => 'active',
                    ]);
                } else {
                    \App\Models\CompanyPackage::create([
                        'company_id' => $company->id,
                        'payment_transaction_id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'remaining_balance' => $transaction->amount,
                        'text_rate' => 0.1000,
                        'template_utility_rate' => 0.3000,
                        'template_auth_rate' => 0.1500,
                        'template_marketing_rate' => 0.5000,
                        'automation_rate' => 0.0500,
                        'status' => 'active',
                    ]);
                }
            }

            // Dispatch monitoring & metrics telemetry event
            event(new \App\Events\Payment\PaymentVerified($transaction, $payload));

            Log::info("Wallet funded and finalized successfully via centralized system", [
                'user_id' => $transaction->user_id,
                'payment_transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'gateway' => $transaction->gateway->value,
                'provider_reference' => $gatewayPaymentId,
            ]);

            return $transaction;
        });
    }

    /**
     * Centralized funding failure method.
     *
     * @param PaymentTransaction $transaction
     * @param string $failureReason
     * @param array $payload
     * @return PaymentTransaction
     */
    public function finalizeFailedFunding(
        PaymentTransaction $transaction,
        string $failureReason,
        array $payload
    ): PaymentTransaction {
        Log::debug("[Wallet Finalization Failure: Start]", [
            'id' => $transaction->id,
            'reason' => $failureReason
        ]);

        return DB::transaction(function () use ($transaction, $failureReason, $payload) {
            // Lock payment transaction row
            $transaction = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->status === PaymentTransactionStatus::SUCCESSFUL) {
                Log::warning("Centralized failure bypassed: Transaction {$transaction->id} is already successful.");
                return $transaction;
            }

            $transaction->update([
                'status' => PaymentTransactionStatus::FAILED,
                'payload' => array_merge($transaction->payload ?? [], [
                    'failure_reason' => $failureReason,
                    'failed_payload' => $payload,
                    'failed_at' => now()->toDateTimeString(),
                ]),
            ]);

            // Dispatch monitoring & alert telemetry event
            event(new \App\Events\Payment\PaymentFailed($transaction, $failureReason, $payload));

            Log::info("Wallet funding failed and finalized successfully", [
                'user_id' => $transaction->user_id,
                'payment_transaction_id' => $transaction->id,
                'reason' => $failureReason,
                'gateway' => $transaction->gateway->value,
            ]);

            return $transaction;
        });
    }

    /**
     * Centralized webhook event router/handler.
     *
     * @param string $event
     * @param array $payload
     * @param string $gatewayName
     */
    public function handleWebhookEvent(string $event, array $payload, string $gatewayName = 'razorpay'): void
    {
        Log::info("Centralized webhook handler processing: {$event} for {$gatewayName}", [
            'event' => $event,
            'gateway' => $gatewayName,
        ]);

        try {
            $driver = $this->resolve($gatewayName);
            
            // Normalize payload
            $dto = $driver->normalizeWebhookPayload($event, $payload);

            Log::debug("Normalized webhook payload", [
                'dto' => $dto
            ]);

            $gatewayOrderId = $dto['gateway_order_id'];
            if (empty($gatewayOrderId)) {
                Log::warning("Webhook normalization result missing order ID. Skipping processing.", [
                    'gateway' => $gatewayName,
                ]);
                return;
            }

            // Find matching payment transaction (using gateway_order_id or id)
            $paymentTransaction = PaymentTransaction::where(function ($query) use ($gatewayOrderId) {
                    $query->where('gateway_order_id', $gatewayOrderId)
                          ->orWhere('id', $gatewayOrderId);
                })
                ->where('gateway', $gatewayName)
                ->first();

            if (!$paymentTransaction) {
                Log::warning("No matching payment transaction found for order ID: {$gatewayOrderId} on {$gatewayName}");
                return;
            }

            // Run within db transaction and pessimism locking
            DB::transaction(function () use ($paymentTransaction, $dto, $payload) {
                // Lock row
                $transaction = PaymentTransaction::where('id', $paymentTransaction->id)->lockForUpdate()->firstOrFail();

                // Idempotency check: exit if already successful
                if ($transaction->status === PaymentTransactionStatus::SUCCESSFUL) {
                    Log::info("Webhook idempotency match: Transaction {$transaction->id} is already successful. Skipping.");
                    return;
                }

                // If event is payment success
                if ($dto['event'] === 'payment_success') {
                    // Validate amount
                    if (abs((float)$dto['amount'] - (float)$transaction->amount) > 0.01) {
                        Log::error("Webhook state mismatch: amount difference", [
                            'transaction_id' => $transaction->id,
                            'expected' => $transaction->amount,
                            'received' => $dto['amount']
                        ]);

                        $this->finalizeFailedFunding(
                            $transaction, 
                            "Amount mismatch: expected {$transaction->amount}, received {$dto['amount']}", 
                            $payload
                        );
                        return;
                    }

                    // Validate currency
                    if (strtoupper($transaction->currency) !== strtoupper($dto['currency'])) {
                        Log::error("Webhook state mismatch: currency mismatch", [
                            'transaction_id' => $transaction->id,
                            'expected' => $transaction->currency,
                            'received' => $dto['currency']
                        ]);

                        $this->finalizeFailedFunding(
                            $transaction, 
                            "Currency mismatch: expected {$transaction->currency}, received {$dto['currency']}", 
                            $payload
                        );
                        return;
                    }

                    // Finalize successfully
                    $this->finalizeSuccessfulFunding(
                        $transaction,
                        $dto['payment_reference'] ?? '',
                        $payload['signature'] ?? 'webhook_verified',
                        $payload
                    );
                } elseif ($dto['event'] === 'payment_failed') {
                    $reason = $dto['failure_reason'] ?? 'Payment gateway reported failure';
                    $this->finalizeFailedFunding($transaction, $reason, $payload);
                }
            });

        } catch (\Exception $e) {
            Log::error("Exception in handleWebhookEvent processing", [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
            throw $e;
        }
    }
}

