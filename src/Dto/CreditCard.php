<?php

declare(strict_types=1);

namespace App\Dto;

readonly class CreditCard
{
    public function __construct(
        #[\SensitiveParameter]
        protected string $cardNumber,
        #[\SensitiveParameter]
        protected string $expiryDate,
        #[\SensitiveParameter]
        protected string $cvv,
        protected string $amount,
    ) {
    }

    public function getExpiryDate(): string
    {
        return $this->expiryDate;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCvv(): string
    {
        return $this->cvv;
    }

    public function showOnlyFourNumbersOfCardNumber(): string
    {
        return substr($this->cardNumber, 0, 4).'XXXX XXXX XXXX';
    }
}
