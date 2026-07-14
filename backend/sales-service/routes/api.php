<?php

use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('ventas/{id}/restore', [VentaController::class, 'restore']);
Route::apiResource('ventas', VentaController::class);
Route::apiResource('clientes', ClienteController::class);
