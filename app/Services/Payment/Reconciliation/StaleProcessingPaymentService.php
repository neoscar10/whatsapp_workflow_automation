<?php

namespace App\Services\Payment\Reconciliation;

use App\Models\PaymentTransaction;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Support\Facades\Log;

class StaleProcessingPaymentService
{
    /**
     * Mark transactions stuck in processing status for an abnormal duration back to pending or failed.
     *
     * @param int $staleMinutesThreshold
     * @return array Summary of processed items
     */
    public function recover(int $staleMinutesThreshold = 30): array
    {
        $staleTransactions = PaymentTransaction::where('status', PaymentTransactionStatus::PROCESSING)
            ->where('updated_at', '<=', now()->subMinutes($staleMinutesThreshold))
            ->get();

        Log::info("StaleProcessingPaymentService analyzing " . $staleTransactions->count() . " processing transactions.");

        $recoveredCount = 0;

        foreach ($staleTransactions as $transaction) {
            // Revert back to pending to allow retry options
            $transaction->update([
                'status' => PaymentTransactionStatus::PENDING,
                'payload' => array_merge($transaction->payload ?? [], [
                    'recovered_from_stale_processing_at' => now()->toDateTimeString(),
                    'recovery_reason' => 'Transaction processing state timed out after ' . $staleMinutesThreshold . ' minutes.'
                ])
            ]);
            $recoveredCount++;
            Log::info("Stale transaction {$transaction->id} recovered back to PENDING.");
        }

        return [
            'total_found' => $staleTransactions->count(),
            'total_recovered' => $recoveredCount,
        ];
    }
}
