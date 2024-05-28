<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Product\ProductDetailController;
use App\Http\Controllers\Product\ProductsByCategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class);
Route::post('login', LoginController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('product-detail', ProductDetailController::class);
    Route::get('products-by-category', ProductsByCategoryController::class);
});
