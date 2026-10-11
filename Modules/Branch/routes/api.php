<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\App\Http\Controllers\BranchController;

Route::middleware('auth:sanctum')->prefix('businesses/{businessId}/branches')->group(function () {
    Route::get('/', [BranchController::class, 'index']);
    Route::post('/', [BranchController::class, 'store']);
    Route::get('/{branchId}', [BranchController::class, 'show']);
    Route::put('/{branchId}', [BranchController::class, 'update']);
    Route::patch('/{branchId}', [BranchController::class, 'update']);
    Route::delete('/{branchId}', [BranchController::class, 'destroy']);
});
