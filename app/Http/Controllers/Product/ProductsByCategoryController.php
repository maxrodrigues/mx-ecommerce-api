<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductsByCategoryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $products = Product::query()
                ->where('category_id', $request->category)
                ->get();

            return new JsonResponse([
                'data' => [
                    'products' => $products->toArray(),
                    'message' => 'Products list retrieved successfully',
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
