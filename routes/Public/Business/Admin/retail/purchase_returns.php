<?php
use App\Http\Controllers\Public\Business\Admin\Retail\PurchaseReturnController;
Route::get('purchasing/purchase-returns/purchase-return-list',[PurchaseReturnController::class,'index'])->name('purchase_returns.index');
Route::post('purchasing/purchase-returns/purchase-return-list',[PurchaseReturnController::class,'store'])->name('purchase_returns.store');
