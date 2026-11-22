<?php

use App\Http\Controllers\Public\Business\Admin\Authorization\AuthorizationController;
use App\Http\Controllers\Public\Business\Admin\Authorization\BusinessUserController;
use App\Http\Controllers\Public\Business\Admin\UserController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;


$domain=config('saas.domain');

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function(){
    Route::middleware(['portal.auth:business_admin', 'auth.session'])->group(function(){
        Route::get('/dashboard',[WorkspaceController::class,'dashboard'])->name('business.admin.dashboard');
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
        Route::prefix('configurations')->name('business.admin.configurations.')->group(function () {
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

        // Retail & e-commerce modules: explicit independent controller/route files.
        Route::name('business.admin.retail.')->group(function(){
            require __DIR__.'/retail/products.php';
            require __DIR__.'/retail/stock.php';
            require __DIR__.'/retail/orders.php';
            require __DIR__.'/retail/pos.php';
            require __DIR__.'/retail/purchases.php';
            require __DIR__.'/retail/returns.php';
            require __DIR__.'/retail/payments.php';
            require __DIR__.'/retail/delivery.php';
            require __DIR__.'/retail/discounts.php';
            require __DIR__.'/retail/accounts.php';
            require __DIR__.'/retail/reports.php';
            require __DIR__.'/retail/purchase_orders.php';
            require __DIR__.'/retail/warehouses.php';
            require __DIR__.'/retail/manual_journals.php';
            require __DIR__.'/retail/expenses.php';
            require __DIR__.'/retail/purchase_returns.php';
            require __DIR__.'/retail/quotations.php';
            require __DIR__.'/retail/marketing.php';
            require __DIR__.'/retail/integrations.php';
            require __DIR__.'/retail/courier.php';
            require __DIR__.'/retail/bank_transfers.php';
            require __DIR__.'/retail/account_groups.php';
        });

        Route::get('/{module}/{menu}/{child?}',[WorkspaceController::class,'feature'])->name('business.admin.feature');   

    });
});
