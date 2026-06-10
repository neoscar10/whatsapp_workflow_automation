<?php

namespace App\Http\Resources\Api\V1\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $company = $user ? $user->company : null;
        $isDemo = $company && $company->status === 'demo';
        $demoCredits = $isDemo ? (float) $company->demo_credits : 0.00;

        $threshold = (float) \App\Models\SystemSetting::get('wallet_threshold', 100.00);
        $currentBalance = $isDemo ? $demoCredits : (float)$this->balance;
        $isLowBalance = $currentBalance < $threshold;

        $totalFunded = $this->transactions()
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('type', WalletTransactionType::CREDIT)
            ->sum('amount');

        $totalSpent = $this->transactions()
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('type', WalletTransactionType::DEBIT)
            ->sum('amount');

        // Fetch recent payment attempts (limit to 5) for retry capability on mobile
        $paymentAttempts = [];
        if ($user) {
            $paymentAttempts = \App\Models\PaymentTransaction::where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($pt) {
                    return [
                        'id' => $pt->id,
                        'amount' => number_format((float)$pt->amount, 2, '.', ''),
                        'status' => $pt->status->value ?? $pt->status,
                        'gateway' => $pt->gateway,
                        'created_at' => $pt->created_at->toIso8601String(),
                    ];
                });
        }

        return [
            'id' => $this->id,
            'balance' => number_format((float)$currentBalance, 4, '.', ''), // Effective balance (Demo or Real)
            'real_balance' => number_format((float)$this->balance, 4, '.', ''), // Strict real balance
            'currency' => $this->currency,
            'status' => $this->status->value,
            'is_demo' => $isDemo,
            'demo_credits' => number_format($demoCredits, 4, '.', ''),
            'is_low_balance' => $isLowBalance,
            'threshold' => number_format($threshold, 2, '.', ''),
            'last_transaction_at' => $this->last_transaction_at ? $this->last_transaction_at->toIso8601String() : null,
            'total_funded' => number_format((float)$totalFunded, 4, '.', ''),
            'total_spent' => number_format((float)$totalSpent, 4, '.', ''),
            'recent_payment_attempts' => $paymentAttempts,
        ];
    }
}
