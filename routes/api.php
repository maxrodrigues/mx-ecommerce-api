<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductDetailController;
use App\Http\Controllers\Product\ProductsByCategoryController;
use App\Http\Controllers\Tags\TagController;
use App\Http\Middleware\AdminUserMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class);
Route::post('login', LoginController::class);

Route::post('admin/login', AdminAuthController::class);
Route::post('admin/register', AdminRegisterController::class);

Route::middleware(['auth:sanctum', AdminUserMiddleware::class])->group(function () {
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::put('products/{sku}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{sku}', [ProductController::class, 'destroy'])->name('products.delete');

    Route::get('categories/{parent_id?}', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category_id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category_id}', [CategoryController::class, 'destroy'])->name('categories.delete');

    Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{tag_id}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag_id}', [TagController::class, 'destroy'])->name('tags.delete');
});

/*Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('product-detail/{sku}', ProductDetailController::class);
    Route::get('products-by-category/{category}', ProductsByCategoryController::class);
});*/
