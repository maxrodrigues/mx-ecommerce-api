<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->parent_id, function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            })
            ->get()
            ->toArray();

        return new JsonResponse([
            'data' => [
                'categories' => $categories,
                'message' => empty($categories) ? 'No categories found' : 'Categories list retrieved successfully',
            ],
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255|unique:categories',
        ]);

        if ($data->fails()) {
            return new JsonResponse([
                'data' => [
                    'message' => 'The given data was invalid.',
                    'errors' => $data->errors(),
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Category::create([
            'name' => $request->name,
            'slug' => $request->name,
            'description' => $request->description
        ]);

        return new JsonResponse([
            'data' => [
                'message' => 'Category created successfully',
            ]
        ], Response::HTTP_CREATED);
    }
}
