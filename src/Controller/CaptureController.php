<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exceptions\AlreayProcessedException;
use App\Exceptions\InvalidAmountException;
use App\Helper\FromRequestToToken;
use App\Service\CaptureService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class CaptureController extends AbstractController
{
    #[Route('/capture', name: 'app_capture', methods: ['POST'])]
    #[OA\Tag('api')]
    #[OA\RequestBody(
        description: 'the token of the credit card and the amount to capture',
        content: [
            new OA\MediaType('multipart/form-data', schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'auth_token',
                        description: 'the token of the credit card with payment',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'amount',
                        description: 'the amount to take from the card number for the transaction',
                        type: 'int',
                    )],
            )),
        ],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the status success and the generated transaction id',
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
                        property: 'transaction_id',
                        description: 'the generated transaction id',
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
                        description: 'an error message : already processed transaction or wrong amount',
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
    public function index(Request $request, CaptureService $captureService): JsonResponse
    {
        $token = new FromRequestToToken($request);
        try {
            return $this->json([
                'status' => 'success',
                'transaction_id' => $captureService->capture($token->getIdentifierWithAmountDto()),
            ]);
        } catch (InvalidAmountException|AlreayProcessedException $e) {
            return $this->json([
                'status' => 'failure',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'failure',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
