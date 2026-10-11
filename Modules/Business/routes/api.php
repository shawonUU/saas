<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\App\Http\Controllers\BusinessController;

Route::middleware('auth:sanctum')->prefix('businesses')->group(function () {
    Route::get('/', [BusinessController::class, 'index']);
    Route::post('/', [BusinessController::class, 'store']);
});

