<?php

declare(strict_types=1);

namespace App\Enum;

enum EnumTransactionOperations: string
{
    case CAPTURE_SUCCESS = 'capture_success';
    case REFUND_SUCCESS = 'refund_success';
}
