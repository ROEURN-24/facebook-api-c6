<?php

use App\Http\Controllers\API\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/me', [AuthController::class, 'index'])->middleware('auth:sanctum');

// Posts routes with authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']); // Use {post} instead of {id}
    Route::put('/posts/{post}', [PostController::class, 'update']); // Use {post} instead of {id}
    Route::delete('/posts/{post}', [PostController::class, 'destroy']); // Use {post} instead of {id}
});