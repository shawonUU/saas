<?php

use App\Http\Controllers\Public\Business\Admin\AuthorizationController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;

$domain=config('saas.domain');

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function(){
    Route::middleware('portal.auth:business_admin')->group(function(){
        Route::get('/dashboard',[WorkspaceController::class,'dashboard'])->name('store.admin.dashboard');
    });
});

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function(){
    Route::middleware('portal.auth:business_admin')->group(function(){
        Route::prefix('authorization')->name('authorization.')->group(function(){
            Route::get('/access-control',[AuthorizationController::class,'index'])->name('access-control');
            Route::get('/access-control/{memberId}',[AuthorizationController::class,'show'])->name('access-control.show');
            Route::post('/access-control/store',[AuthorizationController::class,'store'])->name('access-control.store');
        });

        Route::get('/{module}/{menu}/{child?}',[WorkspaceController::class,'feature'])->name('store.admin.feature');   

    });
});