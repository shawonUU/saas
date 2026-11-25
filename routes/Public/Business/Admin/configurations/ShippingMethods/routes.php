<?php
use Illuminate\Support\Facades\Route;

Route::get('shipping-methods',[\App\Http\Controllers\Public\Business\Admin\Configurations\ShippingMethods\ShippingMethodController::class,'index'])->name('shipping-methods.index');
Route::post('shipping-methods',[\App\Http\Controllers\Public\Business\Admin\Configurations\ShippingMethods\ShippingMethodController::class,'store'])->name('shipping-methods.store');
Route::put('shipping-methods/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\ShippingMethods\ShippingMethodController::class,'update'])->whereNumber('record')->name('shipping-methods.update');
Route::delete('shipping-methods/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\ShippingMethods\ShippingMethodController::class,'destroy'])->whereNumber('record')->name('shipping-methods.destroy');
