
<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\Http\Controllers\BusinessCategoryController;
use Modules\Business\Http\Controllers\BusinessController;
use Modules\Business\Http\Controllers\BusinessDashboardConfigController;
use Modules\Business\Http\Controllers\BusinessDashboardController;
use Modules\Business\Http\Controllers\BusinessMemberPermissionController;
use Modules\Business\Http\Controllers\BusinessTeamController;
use Modules\Business\Http\Controllers\BusinessUserRegistrationController;
use Modules\Business\Http\Controllers\BusinessRoleController;

Route::middleware('auth:sanctum')->group(function () {

    // Business Routes
    Route::prefix('businesses')->group(function () {
        Route::get('/', [BusinessController::class, 'index']);
        Route::post('/', [BusinessController::class, 'store']);

        Route::get('{businessId}/dashboard', [
            BusinessDashboardController::class,
            'show'
        ])->whereNumber('businessId');

        Route::get('{businessId}/dashboard-config', [
            BusinessDashboardConfigController::class,
            'show'
        ])->whereNumber('businessId');

        Route::put('{businessId}/members/{memberId}/permissions', [
            BusinessMemberPermissionController::class,
            'update'
        ])->whereNumber('businessId')->whereNumber('memberId');
    });

    // Business Categories
    Route::prefix('business-categories')->group(function () {
        Route::get('/', [BusinessCategoryController::class, 'index']);
        Route::post('/', [BusinessCategoryController::class, 'store']);
        Route::get('{id}', [BusinessCategoryController::class, 'show'])->whereNumber('id');
        Route::put('{id}', [BusinessCategoryController::class, 'update'])->whereNumber('id');
        Route::patch('{id}', [BusinessCategoryController::class, 'update'])->whereNumber('id');
        Route::delete('{id}', [BusinessCategoryController::class, 'destroy'])->whereNumber('id');
    });

    // Business Team and User Management
    Route::prefix('businesses/{businessId}/team') ->where(['businessId' => '[0-9]+']) ->group(function () {
            Route::get('/', [BusinessTeamController::class, 'index']);
            Route::post('/', [BusinessTeamController::class, 'store']);

            Route::post('create-user', [
                BusinessUserRegistrationController::class,
                'store'
            ]);

            Route::get('{memberId}/permissions', [
                BusinessTeamController::class,
                'permissions'
            ])->whereNumber('memberId');

            Route::put('{memberId}/permissions', [
                BusinessTeamController::class,
                'savePermissions'
            ])->whereNumber('memberId');

            Route::put('{memberId}', [
                BusinessTeamController::class,
                'update'
            ])->whereNumber('memberId');

            Route::delete('{memberId}', [
                BusinessTeamController::class,
                'destroy'
            ])->whereNumber('memberId');
        });

    // Business Custom Roles
    Route::prefix('businesses/{businessId}/roles')
        ->where(['businessId' => '[0-9]+'])
        ->controller(BusinessRoleController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');

            Route::put('{roleId}', 'update')
                ->whereNumber('roleId');

            Route::get('{roleId}/permissions', 'permissions')
                ->whereNumber('roleId');

            Route::put('{roleId}/permissions', 'savePermissions')
                ->whereNumber('roleId');
        });

});
