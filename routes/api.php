<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\API\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\File;

// Post routes prefix
Route::prefix('post')->group(function () {
    Route::get('/list', [PostController::class, 'index'])->name('post.list');
    Route::post('/create', [PostController::class, 'store'])->name('post.create');
    Route::get('/show/{id}', [PostController::class, 'show'])->name('post.show');
    Route::put('/update/{id}', [PostController::class, 'update'])->name('post.update');
    Route::delete('/delete/{id}', [PostController::class, 'destroy'])->name('post.destroy');
});


// Comment routes prefix
Route::prefix('comment')->group(function () {
    Route::get('/list', [CommentController::class, 'index'])->name('comment.list');
    Route::post('/create', [CommentController::class, 'store'])->name('comment.create');
    Route::get('/show/{id}', [CommentController::class, 'show'])->name('comment.show');
    Route::put('/update/{id}', [CommentController::class, 'update'])->name('comment.update');
    Route::delete('/delete/{id}', [CommentController::class, 'destroy'])->name('comment.destroy');
});



// Like routes prefix
Route::prefix('like')->group(function () {
    Route::get('/list', [LikeController::class, 'index'])->name('like.list');
    Route::post('/create', [LikeController::class, 'store'])->name('like.create');
    Route::get('/show/{id}', [LikeController::class, 'show'])->name('like.show');
});




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



Route::get('/storage/{path}', function ($path) {
    // Construct the full file path
    $filePath = storage_path('app/public/' . $path);

    // Check if the file exists
    if (!File::exists($filePath)) {
        abort(404);
    }

    // Determine the MIME type of the file
    $mime = File::mimeType($filePath);

    // Set appropriate headers for the response
    $headers = [
        'Content-Type' => $mime,
    ];

    // Return the file as a response
    return response()->file($filePath, $headers);
})->where('path', '.*');
