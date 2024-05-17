<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', \App\Http\Controllers\Auth\RegisterController::class);
Route::post('login', \App\Http\Controllers\Auth\LoginController::class);
