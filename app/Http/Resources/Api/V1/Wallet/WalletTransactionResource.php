<?php

namespace App\Http\Resources\Api\V1\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type->value,
            'category' => $this->category->value,
            'amount' => number_format((float)$this->amount, 4, '.', ''),
            'balance_before' => number_format((float)$this->balance_before, 4, '.', ''),
            'balance_after' => number_format((float)$this->balance_after, 4, '.', ''),
            'status' => $this->status->value,
            'description' => $this->description,
            'provider_reference' => $this->provider_reference,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
