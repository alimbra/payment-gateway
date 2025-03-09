<?php

declare(strict_types=1);

namespace App\Service\Providers;

use App\Dto\CreditCard;

class ProviderB implements ProviderAuthorizationInterface
{
    public function checkValidity(CreditCard $creditCard): bool
    {
        return true;
    }
}
