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
    Route::delete('questions/archive/{question}', [QuestionController::class, 'archive'])->name('questions.archive');
    // endregion
});
// endrefion
