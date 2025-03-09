<?php

declare(strict_types=1);

namespace App\Tests\functionnalTest\Controller;

use App\Dto\CreditCardWithPayment;
use App\Service\DbManager\DbManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationControllerTest extends WebTestCase
{
    /**
     * @throws JsonException
     */
    public function testAuthorizationControllerSuccess(): void
    {
        $client = self::createClient();
        $client->request(
            method: Request::METHOD_POST,
            uri: '/api/authorization',
            content: json_encode(
                [
                    'card_number' => '5503550355035503',
                    'expiry_date' => '10/26',
                    'cvv' => "405",
                    'amount' => 100000,
                ])
        );
        self::assertResponseIsSuccessful();
        $response = $client->getResponse();
        // we check if the response has both token and status
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('auth_token', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('success', $content['status']);
        // we check if the token refers to the saved CreditCardWithPayment
        $container = self::getContainer()->get(DbManagerInterface::class);
        self::assertInstanceOf(DbManagerInterface::class, $container);
        $creditCard = $container->get($content['auth_token']);
        self::assertInstanceOf(CreditCardWithPayment::class, $creditCard);
        self::assertSame($creditCard->getCardNumber(), '5503550355035503');
    }

    public function testAuthorizationControllerFailWithWrongData(): void
    {
        $client = self::createClient();
        $client->request(
            method: Request::METHOD_POST,
            uri: '/api/authorization',
            content: json_encode(
                [
                    'card_number' => 'wrong card number',
                    'expiry_date' => '10/26',
                    'cvv' => "405",
                    'amount' => 100000,
                ])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
