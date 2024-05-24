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
        $categories = Category::all();
        return new JsonResponse([
            'data' => $categories
        ], Response::HTTP_OK);
    }
}
