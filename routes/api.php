<?php

use App\Http\Controllers\{AuthController, QuestionController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->middleware(['guest', 'web'])->prefix('/auth')->group(function () {
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/login', 'login')->name('auth.login');
    // Route::post('/logout', 'logout')->name('auth.logout');
});

// region Authenticated
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn (Request $request) => $request->user());

    // region questions
    Route::apiResource('questions', QuestionController::class);

    Route::controller(QuestionController::class)->group(function () {
        Route::delete('questions/{question}/archive', 'archive')->name('questions.archive');
        Route::put('questions/{question}/restore', 'restore')->name('questions.restore');

        Route::put('questions/{question}/publish', 'publish')->name('questions.publish');
    });
    // endregion
});
// endrefion
