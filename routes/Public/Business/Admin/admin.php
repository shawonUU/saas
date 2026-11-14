<?php

use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;


$domain = config('saas.domain');
// Reserve admin.saas.test for platform authority before resolving business domains.

// Business admin portals and the wildcard customer storefront are kept independent.
Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->name('store.admin.')->group(function () {
    Route::middleware('portal.auth:business_admin')->group(function () {
        Route::get('/dashboard', [WorkspaceController::class, 'dashboard'])->name('dashboard');
        Route::get('/{moduleSlug}/{menuSlug}/{childSlug?}',[WorkspaceController::class,'feature'])->name('feature');    }); 
});