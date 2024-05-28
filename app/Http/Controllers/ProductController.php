<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     description="Returns all registered products",
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *     ),
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $products = Product::query()
                ->get();

            return new JsonResponse([
                'data' => [
                    'products' => $products->toArray(),
                    'message' => empty($products) ? 'No products found' : 'Products list retrieved successfully',
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
