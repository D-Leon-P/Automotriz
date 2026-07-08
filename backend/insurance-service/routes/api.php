<?php

use App\Http\Controllers\SeguroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('seguros', SeguroController::class);
