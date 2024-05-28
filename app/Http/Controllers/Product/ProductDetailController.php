<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductDetailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/product-detail",
     *     description="Returns the details of a product",
     *
     *     @OA\Parameter(
     *         name="sku",
     *         in="body",
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $product = Product::query()
                ->where('sku', $request->sku)
                ->first();

            if (! $product) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'Product not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'data' => [
                    'product' => $product->toArray(),
                    'message' => 'Product retrieved successfully',
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
