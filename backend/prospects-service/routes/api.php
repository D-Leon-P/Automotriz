<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas de Autenticación
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Rutas de Vehículos
Route::apiResource('vehiculos', VehiculoController::class)->only(['index', 'show']);

// Rutas de Prospectos
Route::apiResource('prospectos', ProspectoController::class);
