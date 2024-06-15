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
