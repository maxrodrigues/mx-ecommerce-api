<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        description: 'Get all products',
        summary: 'Get all products',
        security: [
            [
                'bearerAuth' => []
            ]
        ],
        tags: ['Product'],
        responses: [
            new OA\Response(response: Response::HTTP_OK, description: 'OK'),
        ]
    )]
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

    #[OA\Post(
        path: '/api/products',
        description: 'Create a new product',
        summary: 'Create a new product',
        security: [
            [
                'bearerAuth' => []
            ]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["name", "description", "category_id", "sku", "price", "stock"],
                    properties: [
                        new OA\Property(property: "name", description: "Product name", type: "string", example: "Product Test"),
                        new OA\Property(property: "description", description: "Product description", type: "string", example: "Product description"),
                        new OA\Property(property: "category_id", description: "Product category id", type: "integer", example: 1),
                        new OA\Property(property: "sku", description: "Product sku", type: "string", example: "product-test"),
                        new OA\Property(property: "price", description: "Product price", type: "number", example: 10.00),
                        new OA\Property(property: "stock", description: "Product stock", type: "integer", example: 10),
                    ]
                )
            )
        ),
        tags: ["Product"],
        responses: [
            new OA\Response(response: Response::HTTP_CREATED, description: 'Created'),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Unprocessable Entity'),
            new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal Server Error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'name' => 'required|min:3|max:255',
                'description' => 'required|min:3|max:255',
                'category_id' => 'required|exists:categories,id',
                'sku' => 'required|min:3|max:255|unique:products,sku',
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

    #[OA\Put(
        path: '/api/products/{sku}',
        description: 'Update a product',
        summary: 'Update a product',
        security: [
            [
                'bearerAuth' => []
            ]
        ],
        tags: ['Product'],
        responses: [
            new OA\Response(response: Response::HTTP_OK, description: 'OK'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Not Found'),
            new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal Server Error'),
        ]
    )]
    public function update(Request $request, $sku): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'name' => 'sometimes|min:3|max:255',
                'description' => 'sometimes|min:3|max:255',
                'category_id' => 'sometimes|exists:categories,id',
                'sku' => 'sometimes|min:3|max:255|unique:products,sku',
                'price' => 'sometimes|numeric',
                'stock' => 'sometimes|numeric',
            ]);

            $product = Product::query()
                ->where('sku', $sku)
                ->first();

            if (! $product) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'Product not found or not updated',
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $product->fill($data->validated());
            $product->save();

            return new JsonResponse([
                'data' => [
                    'message' => 'Product updated successfully',
                    'product' => $product->toArray(),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[OA\Delete(
        path: '/api/products/{sku}',
        description: 'Delete a product',
        summary: 'Delete a product',
        security: [
            [
                'bearerAuth' => []
            ]
        ],
        tags: ['Product'],
        responses: [
            new OA\Response(response: Response::HTTP_OK, description: 'OK'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Not Found'),
            new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal Server Error'),
        ]
    )]
    public function destroy($sku): JsonResponse
    {
        try {
            $product = Product::query()
                ->where('sku', $sku)
                ->delete();

            if (! $product) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'Product not found or not deleted',
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'data' => [
                    'message' => 'Product deleted successfully',
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
