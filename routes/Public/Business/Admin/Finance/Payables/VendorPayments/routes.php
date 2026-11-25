<?php
use Illuminate\Support\Facades\Route;

Route::get('payables/vendor-payments',[\App\Http\Controllers\Public\Business\Admin\Finance\PaymentController::class,'index'])->defaults('kind','supplier')->name('payables.vendor-payments.index');
Route::post('payables/vendor-payments',[\App\Http\Controllers\Public\Business\Admin\Finance\PaymentController::class,'store'])->defaults('kind','supplier')->name('payables.vendor-payments.store');
