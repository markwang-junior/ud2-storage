<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorldController;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\CsvController;

Route::apiResource('hello', HelloWorldController::class);
Route::apiResource('json', JsonController::class);


Route::get('/csv', [CsvController::class, 'index']);
Route::post('/csv', [CsvController::class, 'store']);
Route::get('/csv/{id}', [CsvController::class, 'show']);
Route::put('/csv/{id}', [CsvController::class, 'update']);
Route::delete('/csv/{id}', [CsvController::class, 'destroy']);
