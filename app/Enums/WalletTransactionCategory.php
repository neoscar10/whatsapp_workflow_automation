<?php

namespace App\Enums;

enum WalletTransactionCategory: string
{
    case FUNDING = 'funding';
    case USAGE = 'usage';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
}
