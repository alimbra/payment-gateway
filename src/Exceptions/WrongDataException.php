<?php

namespace App\Exceptions;

class WrongDataException extends \Exception
{
    public function __construct(string $message = 'Wrong credit card number or wrong amount. check all the details')
    {
        parent::__construct($message);
    }
}
