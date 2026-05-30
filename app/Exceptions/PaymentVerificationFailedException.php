<?php

namespace App\Exceptions;

use Exception;

class PaymentVerificationFailedException extends Exception
{
    public function __construct(string $message = "Razorpay payment verification failed.", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
