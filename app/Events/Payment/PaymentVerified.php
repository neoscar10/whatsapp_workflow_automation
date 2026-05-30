<?php

namespace App\Events\Payment;

use App\Models\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified
{
    use Dispatchable, SerializesModels;

    public PaymentTransaction $transaction;
    public array $metadata;

    public function __construct(PaymentTransaction $transaction, array $metadata = [])
    {
        $this->transaction = $transaction;
        $this->metadata = $metadata;
    }
}
