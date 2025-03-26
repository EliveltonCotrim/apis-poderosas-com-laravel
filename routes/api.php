<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('throttle:10,1')->get('/teste', function(){
    return response()->json(['message' => 'Hello World!']);
});
