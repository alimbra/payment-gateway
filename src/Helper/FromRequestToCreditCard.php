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
    protected CreditCard $creditCard;

    public function __construct(
        protected Request $request,
    ) {
        /** @var array<string, string> $content */
        $content = json_decode($request->getContent(), true);
        $this->creditCard = new CreditCard(
            cardNumber: $content[self::CARD_NUMBER] ?? '',
            expiryDate: $content[self::EXPIRY_DATE] ?? '',
            ccv: $content[self::CVV] ?? '',
            amount: $content[self::AMOUNT] ?? '',
        );
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }
}
