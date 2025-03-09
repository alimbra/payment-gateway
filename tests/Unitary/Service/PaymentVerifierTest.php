<?php

declare(strict_types=1);

namespace App\Tests\Unitary\Service;

use App\Dto\CreditCard;
use App\Exceptions\WrongDataException;
use App\Service\PaymentVerifier;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PaymentVerifierTest extends KernelTestCase
{
    private static PaymentVerifier $paymentVerifier;
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $paymentVerifier = self::getContainer()->get(PaymentVerifier::class);
        self::assertInstanceOf(PaymentVerifier::class, $paymentVerifier);
        self::$paymentVerifier = $paymentVerifier;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPaymentVerifierSuccess(): void
    {
        $creditCard = new CreditCard(
            cardNumber: '5503550355035503',
            expiryDate: '10/26',
            cvv: '234',
            amount: '100000',
        );
        $result = self::$paymentVerifier->generateToken($creditCard);
        self::assertNotNull($result);
    }

    public function testPaymentVerifierFailureWithWrongData(): void
    {
        $creditCard = new CreditCard(
            cardNumber: '55035503550',
            expiryDate: '10/26',
            cvv: '34',
            amount: '100000',
        );
        $this->expectException(WrongDataException::class);
        self::$paymentVerifier->generateToken($creditCard);
    }

    public function testPaymentVerifierFailureWithCardNumberStartingWith4(): void
    {
        $creditCard = new CreditCard(
            cardNumber: '4503550355023245',
            expiryDate: '10/26',
            cvv: '344',
            amount: '100000',
        );
        self::assertNull(self::$paymentVerifier->generateToken($creditCard));
    }
}
