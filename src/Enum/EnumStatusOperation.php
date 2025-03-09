<?php

declare(strict_types=1);

namespace App\Enum;

enum EnumStatusOperation: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case PROCESSED = 'processed';
    case REFUNDED = 'refunded';
}
