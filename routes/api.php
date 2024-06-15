<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\UserController;

// Authentication routes
Route::post('/register', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'login']);

// Password routes
Route::post('/forget-password', [PasswordController::class, 'forgetPassword']);
Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
Route::put('/change-password', [PasswordController::class, 'changePassword']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::get('/user', [AuthController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes
    Route::put('/user', [UserController::class, 'updateUser']);
    Route::delete('/user', [UserController::class, 'deleteUser']);
    Route::post('/upload-images', [UserController::class, 'uploadImages']);
});
