<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ArmaController;
use App\Http\Controllers\ImpactoController;
use App\Http\Controllers\VerticeController;
use App\Http\Controllers\ObjetivoAreaController;
use App\Http\Controllers\ConfigMapaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('areas', AreaController::class);
Route::apiResource('armas', ArmaController::class);
Route::get('impactos-con-detalles', [ImpactoController::class, 'conDetalles']);
Route::apiResource('impactos', ImpactoController::class);
Route::apiResource('vertices', VerticeController::class);
Route::apiResource('objetivos_area', ObjetivoAreaController::class);
Route::apiResource('config_mapa', ConfigMapaController::class);
