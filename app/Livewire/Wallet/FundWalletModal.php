<?php

namespace App\Livewire\Wallet;

use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentService;
use App\Enums\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class FundWalletModal extends Component
{
    public $show = false;
    public $amount = 500;
    public $gateway = 'razorpay';
    public $transactionId = null;
    public $checkoutData = null;
    public $selectedPackageId = null;

    #[On('open-funding-modal')]
    public function open()
    {
        $this->reset(['transactionId', 'checkoutData']);
        $firstPkg = \App\Models\FundingPackage::where('is_active', true)->orderBy('amount')->first();
        if ($firstPkg) {
            $this->selectedPackageId = $firstPkg->id;
            $this->amount = (float) $firstPkg->amount;
        } else {
            $this->selectedPackageId = null;
            $this->amount = (float) \App\Models\SystemSetting::get('minimum_recharge', 500.00);
        }
        $this->show = true;
    }

    /**
     * Retry an existing pending, failed, or abandoned payment transaction safely.
     *
     * @param string $transactionId
     */
     #[On('retry-payment')]
     public function retryPayment($transactionId)
     {
         $transaction = PaymentTransaction::where('user_id', Auth::id())->findOrFail($transactionId);
         
         // 1. Double check and block successful checkouts from being retried
         if ($transaction->status === \App\Enums\PaymentTransactionStatus::SUCCESSFUL) {
             $this->dispatch('notify', [
                 'type' => 'error',
                 'message' => 'This payment transaction has already succeeded.',
             ]);
             return;
         }

         $this->transactionId = $transaction->id;
         $this->amount = (float) $transaction->amount;
         $this->gateway = $transaction->gateway->value;

         // Mark transaction state as processing during retry
         $transaction->update(['status' => \App\Enums\PaymentTransactionStatus::PROCESSING]);

         if ($this->gateway === 'razorpay') {
             $smallestUnitAmount = (int) bcmul((string) $transaction->amount, '100', 0);
             $keyId = config('payment.gateways.razorpay.key_id');

             $this->checkoutData = [
                 'key' => $keyId ?: 'rzp_test_mockkeyid123',
                 'amount' => $smallestUnitAmount,
                 'currency' => $transaction->currency,
                 'name' => config('app.name', 'WhatsApp Automation'),
                 'description' => "Wallet Funding - Order #{$transaction->id}",
                 'order_id' => $transaction->gateway_order_id,
                 'prefill' => [
                     'name' => Auth::user()->name,
                     'email' => Auth::user()->email,
                 ],
             ];

             $this->show = true;
             
             $this->dispatch('launch-razorpay', [
                 'transaction_id' => $this->transactionId,
                 'checkout_data' => $this->checkoutData,
             ]);
         } elseif ($this->gateway === 'cashfree') {
             $payload = $transaction->payload;
             $sessionId = $payload['checkout_data']['payment_session_id'] ?? ($payload['payment_session_id'] ?? null);

             if (!$sessionId) {
                 // Regenerate session if expired or not found
                 try {
                     $paymentService = app(PaymentService::class);
                     $driver = $paymentService->resolve(\App\Enums\PaymentGateway::CASHFREE);
                     $initResponse = $driver->initializePayment($transaction);
                     
                     $transaction->update([
                         'gateway_order_id' => $initResponse['gateway_order_id'],
                         'payload' => $initResponse,
                     ]);
                     
                     $sessionId = $initResponse['checkout_data']['payment_session_id'] ?? null;
                 } catch (\Exception $e) {
                     $this->dispatch('notify', [
                         'type' => 'error',
                         'message' => 'Failed to regenerate payment session: ' . $e->getMessage(),
                     ]);
                     return;
                 }
             }

             $this->checkoutData = [
                 'payment_session_id' => $sessionId,
                 'order_id' => $transaction->gateway_order_id,
                 'environment' => config('payment.gateways.cashfree.environment', 'sandbox'),
             ];

             $this->show = true;

             $this->dispatch('launch-cashfree', [
                 'transaction_id' => $this->transactionId,
                 'checkout_data' => $this->checkoutData,
             ]);
         }
     }

    public function initializeFunding(PaymentService $paymentService)
    {
        $minRecharge = (float) \App\Models\SystemSetting::get('minimum_recharge', 500.00);
        $activePackages = \App\Models\FundingPackage::where('is_active', true)->get();

        $rules = [
            'gateway' => 'required|string|in:razorpay,cashfree',
        ];

        if ($activePackages->isNotEmpty()) {
            $rules['selectedPackageId'] = [
                'required',
                function ($attribute, $value, $fail) use ($activePackages) {
                    $pkg = $activePackages->firstWhere('id', $value);
                    if (!$pkg) {
                        $fail('Please select a valid package.');
                    }
                }
            ];
        } else {
            $rules['amount'] = 'required|numeric|min:' . $minRecharge;
        }

        $this->validate($rules);

        if ($activePackages->isNotEmpty()) {
            $pkg = $activePackages->firstWhere('id', $this->selectedPackageId);
            $this->amount = (float) $pkg->amount;
        }

        try {
            $response = $paymentService->initializeWalletFunding(
                Auth::user(),
                $this->amount,
                $this->gateway
            );

            $this->transactionId = $response['transaction_id'];
            $this->checkoutData = $response['checkout_data'];

            if ($this->gateway === 'razorpay') {
                $this->dispatch('launch-razorpay', [
                    'transaction_id' => $this->transactionId,
                    'checkout_data' => $this->checkoutData,
                ]);
            } elseif ($this->gateway === 'cashfree') {
                $this->dispatch('launch-cashfree', [
                    'transaction_id' => $this->transactionId,
                    'checkout_data' => $this->checkoutData,
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to initialize payment: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Complete modal states and emit events when payment is verified successfully.
     */
    #[On('payment-verified')]
    public function onPaymentVerified()
    {
        $this->show = false;
        $this->dispatch('wallet-updated');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Wallet funded successfully!'
        ]);
    }

    public function render()
    {
        $packages = \App\Models\FundingPackage::where('is_active', true)->orderBy('amount')->get();
        $minimumRecharge = (float) \App\Models\SystemSetting::get('minimum_recharge', 500.00);

        return view('livewire.wallet.fund-wallet-modal', [
            'packages' => $packages,
            'minimumRecharge' => $minimumRecharge,
        ]);
    }
}
