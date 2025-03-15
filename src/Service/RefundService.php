<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\IdentifierWithAmountDto;
use App\Dto\RefundDto;
use App\Dto\TransactionDto;
use App\Exceptions\AlreayProcessedException as AlreayProcessedExceptionAlias;
use App\Exceptions\UnableToProcessException;
use App\Service\DbManager\DbManagerInterface;
use App\Service\Operation\OperationsPayment;
use App\Service\TokenManager\TokenManager;
use Psr\Log\LoggerInterface;

class RefundService
{
    public function __construct(
        private DbManagerInterface $dbManager,
        private OperationsPayment $operationsPayment,
        private TokenManager $tokenManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function refund(IdentifierWithAmountDto $identifierWithAmountDto): string
    {
        $transactionDto = $this->processRefund($identifierWithAmountDto);

        return $this->createRefund($transactionDto);
    }

    /**
     * @throws AlreayProcessedExceptionAlias
     * @throws UnableToProcessException
     * @throws \Exception
     */
    private function processRefund(IdentifierWithAmountDto $token): TransactionDto
    {
        if (!$token->getToken()) {
            $this->logger->error('transaction id not given');
            throw new \Exception(message: 'Transaction id not given');
        }

        $transactionDto = $this->dbManager->get($token->getToken());
        if (!$transactionDto instanceof TransactionDto) {
            $this->logger->error('Transaction not found with key '.$token->getToken());
            throw new \Exception(message: 'Transaction not found');
        }

        $this->operationsPayment->processRefund($transactionDto);
        $this->dbManager->save($token->getToken(), $transactionDto);

        return $transactionDto;
    }

    /**
     * @throws \Exception
     */
    private function createRefund(TransactionDto $transactionDto): string
    {
        $token = $this->tokenManager->generateToken($transactionDto->getCardNumber());

        $this->dbManager->save(
            id: $token,
            object: new RefundDto(transactionDto: $transactionDto),
        );

        return $token;
    }
}
