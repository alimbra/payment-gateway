<?php

namespace App\Tests\Unitary\Service;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Dto\IdentifierWithAmountDto;
use App\Dto\TransactionDto;
use App\Enum\EnumStatusOperation;
use App\Exceptions\AlreayProcessedException;
use App\Exceptions\InvalidAmountException;
use App\Service\CaptureService;
use App\Service\DbManager\DbManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CaptureServiceTest extends KernelTestCase
{
    private static CaptureService $captureService;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $captureService = self::getContainer()->get(CaptureService::class);
        self::assertInstanceOf(CaptureService::class, $captureService);
        self::$captureService = $captureService;
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

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                cvv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::RESERVED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $result = self::$captureService->capture(
            new IdentifierWithAmountDto(token: 'credit_id', amount: '100000'),
        );

        self::assertInstanceOf(TransactionDto::class, $dbManager->get($result));
        self::assertSame(EnumStatusOperation::PROCESSED->value, $dbManager->get($result)->getStatus());
    }

    public function testCaptureFailureWithInvalidAmount(): void
    {
        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                cvv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::RESERVED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $this->expectException(InvalidAmountException::class);
        self::$captureService->capture(
            new IdentifierWithAmountDto(token: 'credit_id', amount: '100001'),
        );
    }

    public function testCaptureFailureWithAlreadyProcessed(): void
    {
        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                cvv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::PROCESSED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $this->expectException(AlreayProcessedException::class);
        self::$captureService->capture(
            new IdentifierWithAmountDto(token: 'credit_id', amount: '100000'),
        );
    }
}
