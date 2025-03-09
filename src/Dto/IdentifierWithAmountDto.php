<?php

declare(strict_types=1);

namespace App\Dto;

readonly class IdentifierWithAmountDto
{
    public function __construct(protected ?string $token = null, protected ?string $amount = null)
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