<?php

declare(strict_types=1);

namespace App\Tests\functionnalTest\Controller;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Dto\TransactionDto;
use App\Enum\EnumStatusOperation;
use App\Service\DbManager\DbManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureControllerTest extends WebTestCase
{
    /**
     * @throws \Exception
     */
    public function testCaptureControllerSuccess(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                ccv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::RESERVED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/capture',
            content: json_encode(
                [
                    'auth_token' => 'credit_id',
                    'amount' => '100000',
                ])
        );
        self::assertResponseIsSuccessful();
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('transaction_id', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('success', $content['status']);
        $container = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $container);
        $transaction = $container->get($content['transaction_id']);
        self::assertInstanceOf(TransactionDto::class, $transaction);
    }

    /**
     * @throws \JsonException
     */
    public function testCaptureControllerFailureWithInvalidAmount(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                ccv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::RESERVED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/capture',
            content: json_encode(
                [
                    'auth_token' => 'credit_id',
                    'amount' => '100001',
                ])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('failure', $content['status']);
        self::assertSame( 'your amount is invalid', $content['message']);
    }

    /**
     * @throws \JsonException
     */
    public function testCaptureControllerFailureWithAlreadyProcessed(): void
    {
        $client = self::createClient();

        $dbManager = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $dbManager);

        $creditCardWithPayment = new CreditCardWithPayment(
            creditCard: new CreditCard(
                cardNumber: '5503550355035503',
                expiryDate: '10/26',
                ccv: '234',
                amount: '100000',
            ),
            status: EnumStatusOperation::PROCESSED->value,
        );
        $dbManager->save('credit_id', $creditCardWithPayment);

        $client->request(
            method: Request::METHOD_POST,
            uri: '/capture',
            content: json_encode(
                [
                    'auth_token' => 'credit_id',
                    'amount' => '100000',
                ])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('failure', $content['status']);
        self::assertSame('payment already Processed', $content['message']);
    }
}
