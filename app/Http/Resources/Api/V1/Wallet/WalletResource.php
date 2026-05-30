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
        $totalFunded = $this->transactions()
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('type', WalletTransactionType::CREDIT)
            ->sum('amount');

        $totalSpent = $this->transactions()
            ->where('status', WalletTransactionStatus::SUCCESSFUL)
            ->where('type', WalletTransactionType::DEBIT)
            ->sum('amount');

        return [
            'id' => $this->id,
            'balance' => number_format((float)$this->balance, 4, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status->value,
            'last_transaction_at' => $this->last_transaction_at ? $this->last_transaction_at->toIso8601String() : null,
            'total_funded' => number_format((float)$totalFunded, 4, '.', ''),
            'total_spent' => number_format((float)$totalSpent, 4, '.', ''),
        ];
    }
}
