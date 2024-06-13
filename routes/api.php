<?php

use App\Http\Controllers\API\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



//! User creation and login routes
Route::post('/register', [AuthController::class, 'createUser'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//! Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password');
    Route::get('/user', [AuthController::class, 'index'])->name('user.index');
    Route::put('/user', [AuthController::class, 'updateUser'])->name('user.update');
    Route::delete('/user', [AuthController::class, 'deleteUser'])->name('user.delete');
    Route::post('/upload-images', [AuthController::class, 'uploadImages'])->name('upload-images');
});

//! Password reset routes
Route::post('/forget-password', [AuthController::class, 'forgetPassword'])->name('forget-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');



/*

Route::controller(AuthController::class)->group(function () {
    //? Public routes
    Route::post('/register', 'createUser')->name('register');
    Route::post('/login', 'login')->name('login');
    Route::post('/forget-password', 'forgetPassword')->name('forget-password');
    Route::post('/reset-password', 'resetPassword')->name('reset-password');

    //? Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/user', 'index')->name('user.index');
        Route::put('/user', 'updateUser')->name('user.update');
        Route::put('/change-password', 'changePassword')->name('change-password');
        Route::delete('/user', 'deleteUser')->name('user.delete');
        Route::post('/upload-images', 'uploadImages')->name('upload-images');
    });
});
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
