<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/monuments', [MonumentApiController::class, 'getAllMonuments']);

Route::post('/monuments', [MonumentApiController::class, 'addMonument']);

Route::get('/monuments/{id}', [MonumentApiController::class, 'getOneMonument']);

Route::put('monuments/{id}', [MonumentController::class, 'updateMonument']);

Route::delete('monuments/{id}', [MonumentController::class], 'deleteMonument');