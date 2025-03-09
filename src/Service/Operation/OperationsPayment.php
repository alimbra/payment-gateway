<?php

declare(strict_types=1);

namespace App\Service\Operation;

use App\Dto\CreditCardWithPayment;
use App\Dto\TransactionDto;
use App\Enum\EnumStatusOperation;
use App\Enum\EnumTransactionOperations;
use App\Exceptions\AlreayProcessedException;
use App\Exceptions\UnableToProcessException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

readonly class OperationsPayment
{
    public function __construct(
        protected WorkflowInterface $operationPayment,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AlreayProcessedException
     * @throws UnableToProcessException
     */
    public function processCapture(CreditCardWithPayment $creditCard): void
    {
        if (!$this->operationPayment->can($creditCard, EnumTransactionOperations::CAPTURE_SUCCESS->value)) {
            if (EnumStatusOperation::PROCESSED->value === $creditCard->getStatus()) {
                $this->logger->warning('Payment already processed for'.$creditCard->showOnlyFourNumbersOfCardNumber());
                throw new AlreayProcessedException(message: 'payment already Processed');
            }
            $this->logger->warning('unable to process capture for '.$creditCard->showOnlyFourNumbersOfCardNumber());
            throw new UnableToProcessException(message: 'unable to process the capture');
        }

        $this->operationPayment->apply($creditCard, EnumTransactionOperations::CAPTURE_SUCCESS->value);
    }

    /**
     * @throws AlreayProcessedException
     * @throws UnableToProcessException
     */
    public function processRefund(TransactionDto $transactionDto): void
    {
        if (!$this->operationPayment->can($transactionDto, EnumTransactionOperations::REFUND_SUCCESS->value)) {
            if (EnumStatusOperation::REFUNDED->value === $transactionDto->getStatus()) {
                $this->logger->warning('Payment already processed for'.$transactionDto->showOnlyFourNumbersOfCardNumber());
                throw new AlreayProcessedException(message: 'refund already processed');
            }
            $this->logger->warning('unable to process refund for '.$transactionDto->showOnlyFourNumbersOfCardNumber());
            throw new UnableToProcessException(message: 'unable to process the refund');
        }

        $this->operationPayment->apply($transactionDto, EnumTransactionOperations::REFUND_SUCCESS->value);
    }
}
