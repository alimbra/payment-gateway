<?php

declare(strict_types=1);

namespace App\Tests\functionnal\Controller;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Dto\RefundDto;
use App\Dto\TransactionDto;
use App\Enum\EnumStatusOperation;
use App\Service\DbManager\DbManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RefundControllerTest extends WebTestCase
{
    /**
     * @throws \Exception
     */
    public function testRefundControllerSuccess(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $transactionDto = new TransactionDto(
            creditCardWithPayment: new CreditCardWithPayment(
                creditCard: new CreditCard(
                    cardNumber: '5503550355035503',
                    expiryDate: '10/26',
                    cvv: '234',
                    amount: '100000',
                ),
                status: EnumStatusOperation::PROCESSED->value,
            ), amount: '100000');
        $dbManager->save('transaction_id', $transactionDto);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/api/refund',
            content: json_encode(
                [
                    'transaction_id' => 'transaction_id',
                ])
        );
        self::assertResponseIsSuccessful();
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('refund_id', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('success', $content['status']);
        $container = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $container);
        $transaction = $container->get($content['refund_id']);
        self::assertInstanceOf(RefundDto::class, $transaction);
    }

    /**
     * @throws \JsonException
     */
    public function testRefundControllerFailureWithNotFoundTransaction(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/api/refund',
            content: json_encode(
                [
                    'transaction_id' => 'not_exisiting',
                ])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('status', $content);
        self::assertSame('failure', $content['status']);
    }

    /**
     * @throws \JsonException
     */
    public function testRefundControllerFailureWithAlreadyProcessed(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $transactionDto = new TransactionDto(
            creditCardWithPayment: new CreditCardWithPayment(
                creditCard: new CreditCard(
                    cardNumber: '5503550355035503',
                    expiryDate: '10/26',
                    cvv: '234',
                    amount: '100000',
                ),
                status: EnumStatusOperation::REFUNDED->value,
            ), amount: '100000');

        $dbManager->save('transaction_id', $transactionDto);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/api/refund',
            content: json_encode(
                [
                    'transaction_id' => 'transaction_id',
                ])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('failure', $content['status']);
        self::assertSame('refund already processed', $content['message']);
    }
}
