<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    public function __invoke(Request $request, $sku): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'stock' => 'required|integer|min:0',
            ]);

            if ($data->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $data->errors(),
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $product = Product::where('sku', $sku)->first();

            if(! $product) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'Product not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $product->fill($request->all());
            $product->save();

            return new JsonResponse([
                'data' => [
                    'message' => 'Stock updated successfully',
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
