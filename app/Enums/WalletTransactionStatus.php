<?php

namespace App\Enums;

enum WalletTransactionStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';
    case REVERSED = 'reversed';
}
