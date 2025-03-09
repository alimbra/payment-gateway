<?php

declare(strict_types=1);

namespace App\Service\Providers;

use App\Dto\CreditCard;

interface ProviderAuthorizationInterface
{
    /**
     * @throws \Exception
     */
    public function checkValidity(CreditCard $creditCard): bool;
}
