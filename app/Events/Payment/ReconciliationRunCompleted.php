<?php

namespace App\Events\Payment;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReconciliationRunCompleted
{
    use Dispatchable, SerializesModels;

    public array $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }
}
