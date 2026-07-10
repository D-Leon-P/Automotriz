<?php

use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('ventas', VentaController::class);
Route::apiResource('clientes', ClienteController::class);
