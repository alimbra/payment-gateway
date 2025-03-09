<?php

declare(strict_types=1);

namespace App\Dto;

class RefundDto
{
    public function __construct(
        protected TransactionDto $transactionDto,
    ) {
    }

    public function getStatus(): string
    {
        return $this->transactionDto->getStatus();
    }
}