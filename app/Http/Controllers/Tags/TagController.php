<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'name' => 'required|min:3|max:255|unique:tags',
            ]);

            if ($data->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $data->errors(),
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Tag::create($data->validated());

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
