<?php

use App\Http\Controllers\{AuthController, QuestionController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->prefix('/auth')->group(function () {
    Route::post('/register', 'register')->name('auth.register');
    // Route::post('/login', 'login')->name('auth.login');
    // Route::post('/logout', 'logout')->name('auth.logout');
});
Route::prefix('/auth')->middleware(['guest', 'web'])->group(function () {
});

// region Authenticated
Route::middleware('auth:sanctum')->group(function () {
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
