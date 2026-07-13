<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\AlertaController;
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
Route::get('prospectos/inactivos', [AlertaController::class, 'getInactivos']);
Route::apiResource('prospectos', ProspectoController::class);

// Rutas de Roles y Permisos
Route::get('permisos', [RolController::class, 'getAllPermisos']);
Route::apiResource('roles', RolController::class);

// Rutas de Empleados
Route::apiResource('empleados', EmpleadoController::class);

// Ruta de Alertas (n8n System Integration)
Route::post('alertas/inactividad', [AlertaController::class, 'procesarInactividad']);



