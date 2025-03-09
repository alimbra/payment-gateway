<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exceptions\WrongDataException;
use App\Helper\FromRequestToCreditCard;
use App\Service\PaymentVerifier;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class AuthorizationController extends AbstractController
{
    #[Route('/authorization', name: 'app_authorization', methods: ['POST'])]
    #[OA\Tag('api')]
    #[OA\RequestBody(
        description: 'Content of the credit card',
        content: [
            new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'card_number',
                        description: 'the card number',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'expiry_date',
                        description: 'the expiration date of the credit card ',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'cvv',
                        description: 'the cvv of the credit card ',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'amount',
                        description: 'the amount to take from the card number',
                        type: 'int',
                    )],
            )),
        ],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the status success and the generated auth',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        description: 'success',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'auth_token',
                        description: 'the generate token of the credit Card With Payment',
                        type: 'string',
                    ),
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'return an error message : Wrong credit card number or wrong amount',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        description: 'failure',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'an error message : Wrong credit card number or wrong amount',
                        type: 'string',
                    ),
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 500,
        description: 'return an error internal error message',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'status',
                        description: 'failure',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'an internal error message',
                        type: 'string',
                    ),
                ]
            )
        ),
    )]
    public function index(
        Request $request,
        PaymentVerifier $paymentVerifier,
    ): JsonResponse {
        $dataFiltered = new FromRequestToCreditCard($request);
        $creditCard = $dataFiltered->getCreditCard();

        try {
            return $this->json([
                'status' => 'success',
                'auth_token' => $paymentVerifier->generateToken($creditCard),
            ]);
        } catch (\Throwable $e) {
            if ($e instanceof WrongDataException) {
                return $this->json([
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }

            return $this->json([
                'status' => 'failure',
                'message' => 'internal server error. call the service',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
