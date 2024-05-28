<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     description="Returns all registered products",
     *
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

    public function store(Request $request): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'name' => 'required|min:3|max:255',
                'description' => 'required|min:3|max:255',
                'category_id' => 'required|exists:categories,id',
                'sku' => 'required|min:3|max:255',
                'price' => 'required|numeric',
                'stock' => 'required|numeric',
            ]);

            if ($data->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $data->errors(),
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = collect($request->all())
                ->merge([
                    'slug' => Str::slug($request->name),
                ]);

            Product::create($data->toArray());

            return new JsonResponse([
                'data' => [
                    'message' => 'Product created successfully',
                ],
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
