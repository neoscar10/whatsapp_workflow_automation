<?php

namespace App\Services\Payment\Reconciliation;

use App\Models\PaymentTransaction;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Support\Facades\Log;

class AbandonedPaymentService
{
    /**
     * Mark long-stale pending or processing checkouts as abandoned.
     *
     * @param int $abandonedHoursThreshold
     * @return array Summary of processed items
     */
    public function clean(int $abandonedHoursThreshold = 24): array
    {
        $staleTransactions = PaymentTransaction::whereIn('status', [
            PaymentTransactionStatus::PENDING,
            PaymentTransactionStatus::PROCESSING
        ])
        ->where('created_at', '<=', now()->subHours($abandonedHoursThreshold))
        ->get();

        Log::info("AbandonedPaymentService checking " . $staleTransactions->count() . " potentially abandoned transactions.");

        $markedCount = 0;

        foreach ($staleTransactions as $transaction) {
            $transaction->update([
                'status' => PaymentTransactionStatus::ABANDONED,
                'payload' => array_merge($transaction->payload ?? [], [
                    'abandoned_at' => now()->toDateTimeString(),
                    'abandoned_reason' => 'Transaction was stale for over ' . $abandonedHoursThreshold . ' hours.'
                ])
            ]);
            $markedCount++;
            Log::info("Transaction {$transaction->id} marked as ABANDONED.");
        }

        return [
            'total_found' => $staleTransactions->count(),
            'total_marked_abandoned' => $markedCount,
        ];
    }
}
