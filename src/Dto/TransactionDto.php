<?php

declare(strict_types=1);

namespace App\Dto;

class TransactionDto
{
    public function __construct(
        protected CreditCardWithPayment $creditCardWithPayment,
        readonly string $amount,
    ) {
    }

    public function getStatus(): string
    {
        return $this->creditCardWithPayment->getStatus();
    }

    public function getCardNumber(): string
    {
        return $this->creditCardWithPayment->getCardNumber();
    }
    public function setStatus(string $status): void
    {
        $this->creditCardWithPayment->setStatus($status);
    }

    public function showOnlyFourNumbersOfCardNumber(): string
    {
        return $this->creditCardWithPayment->showOnlyFourNumbersOfCardNumber();
    }
}