<?php

use App\Http\Controllers\Public\Business\Admin\AuthorizationController;
use App\Http\Controllers\Public\Business\Admin\BusinessUserController;
use App\Http\Controllers\Public\Business\Admin\UserController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;


$domain=config('saas.domain');

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function(){
    Route::middleware(['portal.auth:business_admin', 'auth.session'])->group(function(){
        Route::get('/dashboard',[WorkspaceController::class,'dashboard'])->name('store.admin.dashboard');
    });
});

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function(){
    Route::middleware(['portal.auth:business_admin', 'auth.session'])->group(function(){
        Route::prefix('authorization')->name('authorization.')->group(function(){
            Route::get('/access-control',[AuthorizationController::class,'index'])->name('access-control');
            Route::get('/access-control/{memberId}',[AuthorizationController::class,'show'])->name('access-control.show');
            Route::post('/access-control/store',[AuthorizationController::class,'store'])->name('access-control.store');
            Route::get('/users',[BusinessUserController::class,'index'])->name('users.index');
            Route::post('/users',[BusinessUserController::class,'store'])->name('users.store');
            Route::put('/users/{memberId}',[BusinessUserController::class,'update'])->name('users.update');
            Route::delete('/users/{memberId}',[BusinessUserController::class,'destroy'])->name('users.destroy');
        });

        // Independent CRUD route files; deliberately registered before the generic feature route.
        Route::prefix('configurations')->name('store.admin.configurations.')->group(function () {
            require __DIR__.'/configurations/customers.php';
            require __DIR__.'/configurations/suppliers.php';
            require __DIR__.'/configurations/units.php';
            require __DIR__.'/configurations/sizes.php';
            require __DIR__.'/configurations/colors.php';
            require __DIR__.'/configurations/brands.php';
            require __DIR__.'/configurations/categories.php';
            require __DIR__.'/configurations/sub_categories.php';
            require __DIR__.'/configurations/taxes.php';
            require __DIR__.'/configurations/currencies.php';
            require __DIR__.'/configurations/payment_methods.php';
            require __DIR__.'/configurations/shipping_methods.php';
        });

        Route::get('/{module}/{menu}/{child?}',[WorkspaceController::class,'feature'])->name('store.admin.feature');   

    });
});
