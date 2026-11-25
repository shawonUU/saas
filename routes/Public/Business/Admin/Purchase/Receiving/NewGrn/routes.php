<?php
use Illuminate\Support\Facades\Route;

Route::get('receiving/new-grn',[\App\Http\Controllers\Public\Business\Admin\Purchase\Receiving\PurchaseController::class,'create'])->name('receiving.new-grn.create');
Route::post('receiving/new-grn',[\App\Http\Controllers\Public\Business\Admin\Purchase\Receiving\PurchaseController::class,'store'])->name('receiving.new-grn.store');
