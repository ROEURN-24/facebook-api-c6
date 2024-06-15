<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
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
