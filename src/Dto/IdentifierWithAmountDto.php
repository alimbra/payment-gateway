<?php

declare(strict_types=1);

namespace App\Dto;

readonly class IdentifierWithAmountDto
{
    public function __construct(private ?string $token = null, private ?string $amount = null)
    {
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }
}