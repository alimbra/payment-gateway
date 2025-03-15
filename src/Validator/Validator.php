<?php

declare(strict_types=1);

namespace App\Validator;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Exceptions\InvalidAmountException;
use Psr\Log\LoggerInterface;

class Validator
{
    public const MIN_AMOUNT = 10000;
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws InvalidAmountException
     */
    public function checkAmountValidityForCapture(CreditCardWithPayment $creditCard, ?string $amount): void
    {
        if (null === $amount || $amount !== $creditCard->getAmount()) {
            $this->logger->error('Invalid Amount for '.$creditCard->showOnlyFourNumbersOfCardNumber());
            throw new InvalidAmountException();
        }
    }

    public function ChekCardInfos(CreditCard $creditCard): bool
    {
        return
            preg_match('/[0-9]{16}$/', $creditCard->getCardNumber())
            && preg_match('/[0-9]{3}$/', $creditCard->getCvv())
            && preg_match('/[0-1][0-2]\/[0-9]{2}$/', $creditCard->getExpiryDate())
            && (int) $creditCard->getAmount() >= self::MIN_AMOUNT;
    }
}
