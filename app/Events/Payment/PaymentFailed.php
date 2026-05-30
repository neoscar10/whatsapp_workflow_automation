<?php

namespace App\Events\Payment;

use App\Models\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public PaymentTransaction $transaction;
    public string $reason;
    public array $metadata;

    public function __construct(PaymentTransaction $transaction, string $reason, array $metadata = [])
    {
        $this->transaction = $transaction;
        $this->reason = $reason;
        $this->metadata = $metadata;
    }
}
