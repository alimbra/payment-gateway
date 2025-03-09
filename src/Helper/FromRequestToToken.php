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
        /** @var array<string, string> $content */
        $content = json_decode($request->getContent(), true);
        $this->identifierWithAmountDto = new IdentifierWithAmountDto(
            token: $content[$tokenName] ?? '', amount: $content[self::AMOUNT_NAME] ?? ''
        );
    }

    public function getIdentifierWithAmountDto(): IdentifierWithAmountDto
    {
        return $this->identifierWithAmountDto;
    }
}
