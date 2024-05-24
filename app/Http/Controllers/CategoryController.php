<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::all()->toArray();
        if (! $categories) {
            $message = 'No categories found';
        }

        return new JsonResponse([
            'data' => [
                'categories' => $categories,
                'message' => $message ?? 'Categories list retrieved successfully',
            ],
        ], Response::HTTP_OK);
    }
}
