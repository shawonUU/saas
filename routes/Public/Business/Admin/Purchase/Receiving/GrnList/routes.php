<?php
use Illuminate\Support\Facades\Route;

Route::get('receiving/grn-list',[\App\Http\Controllers\Public\Business\Admin\Purchase\Receiving\PurchaseController::class,'index'])->name('receiving.grn-list.index');
