<?php

declare(strict_types=1);

namespace App\Helper;

use App\Dto\CreditCard;
use Symfony\Component\HttpFoundation\Request;

class FromRequestToCreditCard
{
    public const CARD_NUMBER = 'card_number';
    public const EXPIRY_DATE = 'expiry_date';
    public const CVV = 'cvv';
    public const AMOUNT = 'amount';
    private CreditCard $creditCard;

    public function __construct(
        Request $request,
    ) {
        /** @var array{card_number: string, expiry_date: string, cvv: string, amount: int} $content */
        $content = json_decode($request->getContent(), true);
        $this->creditCard = new CreditCard(
            cardNumber: $content[self::CARD_NUMBER],
            expiryDate: $content[self::EXPIRY_DATE],
            cvv: $content[self::CVV],
            amount: (string) $content[self::AMOUNT],
        );
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }
}
