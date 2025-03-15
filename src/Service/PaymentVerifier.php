<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreditCard;
use App\Dto\CreditCardWithPayment;
use App\Exceptions\WrongDataException;
use App\Service\DbManager\DbManagerInterface;
use App\Service\Providers\ProviderA;
use App\Service\Providers\ProviderAuthorizationInterface;
use App\Service\Providers\ProviderB;
use App\Service\TokenManager\TokenManager;
use App\Validator\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

readonly class PaymentVerifier
{
    public function __construct(
        private ContainerInterface $container,
        private TokenManager $tokenManager,
        private DbManagerInterface $dbManager,
        private LoggerInterface $logger,
        private Validator $validator,
        #[Autowire(env: 'PROVIDER_BALANCE')] private string $providerBalance,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function generateToken(CreditCard $creditCard): ?string
    {
        if (!$this->validator->ChekCardInfos($creditCard)) {
            $this->logger->error('Wrong credit card number or wrong amount. check all the details');
            throw new WrongDataException();
        }
        $service = $this->getProvider();

        if ($service->checkValidity($creditCard)) {
            $token = $this->tokenManager->generateToken($creditCard->getCardNumber());
            $this->dbManager->save($token, new CreditCardWithPayment($creditCard));
            $this->logger->info('Token generated for the card number : '.$creditCard->showOnlyFourNumbersOfCardNumber());

            return $token;
        }

        return null;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getProvider(): ProviderAuthorizationInterface
    {
        $rand = rand(1, 100);
        $balance = preg_match('/^-?[0-9]+$/', $this->providerBalance) ? (int) $this->providerBalance : 60;

        $service = match (true) {
            $rand <= $balance => $this->container->get(ProviderA::class),
            $rand > $balance => $this->container->get(ProviderB::class),
            default => null,
        };

        if (!$service instanceof ProviderAuthorizationInterface) {
            $this->logger->error('Provider authorization not found');
            throw new ServiceNotFoundException('Provider authorization not found');
        }

        return $service;
    }
}
