<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreditCardWithPayment;
use App\Dto\IdentifierWithAmountDto;
use App\Dto\TransactionDto;
use App\Exceptions\AlreayProcessedException;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\UnableToProcessException;
use App\Service\DbManager\DbManagerInterface;
use App\Service\Operation\OperationsPayment;
use App\Service\TokenManager\TokenManager;
use App\Validator\Validator;
use Psr\Log\LoggerInterface;

readonly class CaptureService
{
    public function __construct(
        private DbManagerInterface $dbManager,
        private OperationsPayment $operationsPayment,
        private TokenManager $tokenManager,
        private Validator $validator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function capture(IdentifierWithAmountDto $token): string
    {
        $creditCardPayment = $this->processCapture($token);

        return $this->createTransaction($creditCardPayment, $creditCardPayment->getAmount());
    }

    /**
     * @throws InvalidAmountException
     * @throws AlreayProcessedException
     * @throws UnableToProcessException
     * @throws \Exception
     */
    private function processCapture(IdentifierWithAmountDto $token): CreditCardWithPayment
    {
        if (!$token->getToken()) {
            $this->logger->error('Credit Card With Payment id not given');
            throw new \Exception(message: 'Credit Card With Payment  id not given');
        }

        $creditCardPayment = $this->dbManager->get($token->getToken());
        if (!$creditCardPayment instanceof CreditCardWithPayment) {
            $this->logger->error('CreditCardWithPayment not found with key '.$token->getToken());
            throw new \Exception(message: 'Payment not found');
        }

        $this->validator->checkAmountValidityForCapture($creditCardPayment, $token->getAmount());
        $this->operationsPayment->processCapture($creditCardPayment);
        $this->dbManager->save($token->getToken(), $creditCardPayment);

        return $creditCardPayment;
    }

    /**
     * @throws \Exception
     */
    private function createTransaction(CreditCardWithPayment $creditCardWithPayment, string $amount): string
    {
        $token = $this->tokenManager->generateToken($creditCardWithPayment->getCardNumber());

        $this->dbManager->save(
            id: $token,
            object: new TransactionDto(creditCardWithPayment: $creditCardWithPayment, amount: $amount),
        );

        return $token;
    }
}
