<?php

namespace App\Http\Resources\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
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
            'gateway' => $this->gateway->value,
            'amount' => number_format((float)$this->amount, 4, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status->value,
            'gateway_order_id' => $this->gateway_order_id,
            'gateway_payment_id' => $this->gateway_payment_id,
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
