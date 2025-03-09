<?php

declare(strict_types=1);

namespace App\Exceptions;

class InvalidAmountException extends \Exception
{
    public function __construct(string $message = 'your amount is invalid')
    {
        parent::__construct($message);
    }
}