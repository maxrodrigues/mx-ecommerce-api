<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
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
}
