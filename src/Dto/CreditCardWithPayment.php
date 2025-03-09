<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\EnumStatusOperation;

class CreditCardWithPayment
{
    public function __construct(
        readonly protected CreditCard $creditCard,
        protected string $status = EnumStatusOperation::RESERVED->value,
    ) {
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCardNumber(): string
    {
        return $this->creditCard->getCardNumber();
    }
    public function getAmount(): string
    {
        return $this->creditCard->getAmount();
    }

    public function showOnlyFourNumbersOfCardNumber(): string
    {
        return $this->creditCard->showOnlyFourNumbersOfCardNumber();
    }
}
