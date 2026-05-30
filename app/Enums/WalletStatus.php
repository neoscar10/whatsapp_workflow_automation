<?php

namespace App\Enums;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case CLOSED = 'closed';
}
