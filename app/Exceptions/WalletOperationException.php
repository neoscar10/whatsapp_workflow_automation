<?php

namespace App\Exceptions;

use Exception;

class WalletOperationException extends Exception
{
    public function __construct(string $message = "Wallet operation failed.", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
