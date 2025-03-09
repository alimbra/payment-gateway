<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exceptions\AlreayProcessedException;
use App\Exceptions\InvalidAmountException;
use App\Helper\FromRequestToToken;
use App\Service\RefundService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class RefundController extends AbstractController
{
    #[Route('/refund', name: 'app_refund', methods: ['POST'])]
    #[OA\Tag('api')]
    #[OA\RequestBody(
        description: 'the id of transaction',
        content: [
            new OA\MediaType('application/json', schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'transaction_id',
                        description: 'the id of the transaction',
                        type: 'string',
                    )],
            )),
        ],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the status success and the generated refund id',
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
                        property: 'refund_id',
                        description: 'the generated refund id',
                        type: 'string',
                    ),
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'return an error message if transaction already processed refund',
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
                        description: 'an error message : already processed refund',
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
    public function index(Request $request, RefundService $refundService): JsonResponse
    {
        $token = new FromRequestToToken($request, 'transaction_id');

        try {
            return $this->json([
                'status' => 'success',
                'refund_id' => $refundService->refund($token->getIdentifierWithAmountDto()),
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
