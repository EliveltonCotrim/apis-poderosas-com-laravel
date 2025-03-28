<?php

use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('throttle:10,1')->get('/teste', function () {
    return response()->json(['message' => 'Hello World!']);
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
