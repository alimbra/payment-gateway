<?php

declare(strict_types=1);

namespace App\Exceptions;

class UnhandledPaymentException extends \Exception
{
    public function __construct(string $message = 'the card does not start with 5 or 4')
    {
        parent::__construct($message);
    }
}
