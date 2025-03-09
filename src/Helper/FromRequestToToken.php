<?php

declare(strict_types=1);

namespace App\Helper;

use App\Dto\IdentifierWithAmountDto;
use Symfony\Component\HttpFoundation\Request;

class FromRequestToToken
{
    public const TOKEN_NAME = 'auth_token';
    public const AMOUNT_NAME = 'amount';
    protected IdentifierWithAmountDto $identifierWithAmountDto;

    public function __construct(
        protected Request $request,
        protected string $tokenName = self::TOKEN_NAME,
    ) {
        /** @var array{string}|array{string, amount: int} $content */
        $content = json_decode($request->getContent(), true);
        $token = isset($content[$tokenName]) && is_string($content[$tokenName]) ? $content[$tokenName] : null;
        $this->identifierWithAmountDto = new IdentifierWithAmountDto(
            token: $token,
            amount: !isset($content[self::AMOUNT_NAME]) ? '' : (string) $content[self::AMOUNT_NAME],
        );
    }

    public function getIdentifierWithAmountDto(): IdentifierWithAmountDto
    {
        return $this->identifierWithAmountDto;
    }
}
