<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'name' => 'required|string|unique:offers,name',
                'code' => 'required|string|min:3|max:20|unique:offers,code',
                'discount' => 'required|numeric',
                'start_at' => 'required',
                'finish_at' => 'required',
            ]);

            if ($data->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $data->errors()
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Offer::create($request->all());
            return new JsonResponse([
                'data' => [
                    'message' => 'Offer created successfully'
                ]
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $offer_id): JsonResponse
    {
        try {
            $offer = Offer::where('id', $offer_id)->first();

            if (! $offer) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'Offer not found'
                    ]
                ], Response::HTTP_NOT_FOUND);

            }

            $offer->fill($request->all());
            $offer->save();

            return new JsonResponse([
                'data' => [
                    'message' => 'Offer updated successfully'
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
