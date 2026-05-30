<?php

namespace App\Exceptions;

use Exception;

class DuplicatePaymentException extends Exception
{
    public function __construct(string $message = "This payment transaction has already been verified and processed.", int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
