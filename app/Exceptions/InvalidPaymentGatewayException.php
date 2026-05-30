<?php

namespace App\Exceptions;

use Exception;

class InvalidPaymentGatewayException extends Exception
{
    public function __construct(string $message = "The selected payment gateway is invalid or not supported.", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
