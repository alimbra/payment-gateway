<?php

declare(strict_types=1);

namespace App\Service\Providers;

use App\Dto\CreditCard;
use App\Exceptions\UnhandledPaymentException;
use App\Exceptions\WrongDataException;

final class ProviderA implements ProviderAuthorizationInterface
{
    public const NUMBER_FOUR = '4';
    public const NUMBER_FIVE = '5';

    public function checkValidity(CreditCard $creditCard): bool
    {
        if (str_starts_with($creditCard->getCardNumber(), self::NUMBER_FIVE)) {
            return true;
        }

        if (str_starts_with($creditCard->getCardNumber(), self::NUMBER_FOUR)) {
            return false;
        }

        throw new UnhandledPaymentException();
    }
}
