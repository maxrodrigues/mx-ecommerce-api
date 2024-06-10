<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            Tag::create([
                'name' => $request->name,
            ]);

            return new JsonResponse([
                'data' => [
                    'message' => 'Tag created successfully',
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
