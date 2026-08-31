<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayUCallbackController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle PayU SURL / FURL POST redirection callback from browser.
     */
    public function handle(Request $request)
    {
        $allParams = $request->all();
        $txnid = $allParams['txnid'] ?? $request->query('txnid') ?? '';
        $status = strtolower($allParams['status'] ?? $request->query('status') ?? '');

        Log::info("PayU callback received from browser redirection", [
            'txnid' => $txnid,
            'status' => $status,
            'has_hash' => isset($allParams['hash']),
        ]);

        if (empty($txnid)) {
            return redirect()->route('wallet.index')->with('error', 'Invalid payment callback parameters.');
        }

        try {
            $transaction = \App\Models\PaymentTransaction::find($txnid);

            if (!$transaction) {
                return redirect()->route('wallet.index')->with('error', 'Payment transaction not found.');
            }

            // Restore user session if browser withheld SameSite=Lax cookie during cross-site POST
            if (!auth()->check() && $transaction->user) {
                auth()->login($transaction->user);
                $request->session()->regenerate();
            }

            if ($transaction->status === \App\Enums\PaymentTransactionStatus::SUCCESSFUL) {
                return redirect()->route('wallet.index')->with('success', 'Payment successful! Your wallet has been credited.');
            }

            if ($status === 'success') {
                // Perform verification and credit wallet
                $this->paymentService->verifyWalletFunding($transaction->id, $allParams);
                return redirect()->route('wallet.index')->with('success', 'Payment successful! Your wallet has been credited.');
            } else {
                // Finalize failed funding state
                $failureReason = $allParams['error_Message'] ?? $allParams['msg'] ?? 'Payment failed or cancelled on PayU.';
                $this->paymentService->finalizeFailedFunding($transaction, $failureReason, $allParams);
                return redirect()->route('wallet.index')->with('error', 'Payment was not successful: ' . $failureReason);
            }
        } catch (\Exception $e) {
            Log::error("Exception processing PayU callback redirection", [
                'txnid' => $txnid,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('wallet.index')->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }
}
