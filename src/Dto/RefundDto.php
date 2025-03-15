<?php

declare(strict_types=1);

namespace App\Dto;

readonly class RefundDto
{
    public function __construct(
        private TransactionDto $transactionDto,
    ) {
    }

    public function getStatus(): string
    {
        return $this->transactionDto->getStatus();
    }
}