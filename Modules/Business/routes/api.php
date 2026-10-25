
<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\Http\Controllers\BusinessCategoryController;
use Modules\Business\Http\Controllers\BusinessController;
use Modules\Business\Http\Controllers\BusinessDashboardConfigController;
use Modules\Business\Http\Controllers\BusinessDashboardController;
use Modules\Business\Http\Controllers\BusinessMemberPermissionController;


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('businesses')->group(function () {
        Route::get('/', [BusinessController::class, 'index']);
        Route::post('/', [BusinessController::class, 'store']);

        Route::get(
            '/{businessId}/dashboard',
            [BusinessDashboardController::class, 'show']
        );
        Route::get( '/{businessId}/dashboard-config', [BusinessDashboardConfigController::class, 'show']);
        Route::put( '/{businessId}/members/{memberId}/permissions', [BusinessMemberPermissionController::class, 'update'] );
    });

    Route::prefix('business-categories')->group(function () {
        Route::get('/', [BusinessCategoryController::class, 'index']);
        Route::post('/', [BusinessCategoryController::class, 'store']);
        Route::get('/{id}', [BusinessCategoryController::class, 'show']);
        Route::put('/{id}', [BusinessCategoryController::class, 'update']);
        Route::patch('/{id}', [BusinessCategoryController::class, 'update']);
        Route::delete('/{id}', [BusinessCategoryController::class, 'destroy']);
    });
});
