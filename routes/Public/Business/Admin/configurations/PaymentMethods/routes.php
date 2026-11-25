<?php
use Illuminate\Support\Facades\Route;

Route::get('payment-methods',[\App\Http\Controllers\Public\Business\Admin\Configurations\PaymentMethods\PaymentMethodController::class,'index'])->name('payment-methods.index');
Route::post('payment-methods',[\App\Http\Controllers\Public\Business\Admin\Configurations\PaymentMethods\PaymentMethodController::class,'store'])->name('payment-methods.store');
Route::put('payment-methods/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\PaymentMethods\PaymentMethodController::class,'update'])->whereNumber('record')->name('payment-methods.update');
Route::delete('payment-methods/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\PaymentMethods\PaymentMethodController::class,'destroy'])->whereNumber('record')->name('payment-methods.destroy');
