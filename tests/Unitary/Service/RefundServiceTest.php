<?php

declare(strict_types=1);

namespace App\Tests\Unitary\Service;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Dto\IdentifierWithAmountDto;
use App\Dto\RefundDto;
use App\Dto\TransactionDto;
use App\Enum\EnumStatusOperation;
use App\Exceptions\AlreayProcessedException;
use App\Exceptions\InvalidAmountException;
use App\Service\DbManager\DbManagerInterface;
use App\Service\RefundService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RefundServiceTest extends KernelTestCase
{
    private static RefundService $refundService;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $refundService = self::getContainer()->get(RefundService::class);
        self::assertInstanceOf(RefundService::class, $refundService);
        self::$refundService = $refundService;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function testCaptureSuccess(): void
    {
        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $transactionDto = new TransactionDto(
            creditCardWithPayment: new CreditCardWithPayment(
                creditCard: new CreditCard(
                    cardNumber: '5503550355035503',
                    expiryDate: '10/26',
                    ccv: '234',
                    amount: '100000',
                ),
                status: EnumStatusOperation::PROCESSED->value,
            ), amount: '100000');
        $dbManager->save('transaction_id', $transactionDto);

        $result = self::$refundService->refund(
            new IdentifierWithAmountDto(token: 'transaction_id'),
        );

        self::assertInstanceOf(RefundDto::class, $dbManager->get($result));
        self::assertSame(EnumStatusOperation::REFUNDED->value, $dbManager->get($result)->getStatus());
    }

    public function testRefundFailureWithAlreadyProcessed(): void
    {
        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $transactionDto = new TransactionDto(
            creditCardWithPayment: new CreditCardWithPayment(
                creditCard: new CreditCard(
                    cardNumber: '5503550355035503',
                    expiryDate: '10/26',
                    ccv: '234',
                    amount: '100000',
                ),
                status: EnumStatusOperation::REFUNDED->value,
            ), amount: '100000');
        $dbManager->save('transaction_id', $transactionDto);

        $this->expectException(AlreayProcessedException::class);
        self::$refundService->refund(
            new IdentifierWithAmountDto(token: 'transaction_id'),
        );
    }

    public function testRefundFailureWithNotFoundTransaction(): void
    {
        $this->expectException(\Exception::class);
        self::$refundService->refund(
            new IdentifierWithAmountDto(token: 'transaction_id'),
        );
    }
}
