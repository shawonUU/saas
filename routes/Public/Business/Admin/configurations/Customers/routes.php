<?php
use Illuminate\Support\Facades\Route;

Route::get('customers',[\App\Http\Controllers\Public\Business\Admin\Configurations\Customers\CustomerController::class,'index'])->name('customers.index');
Route::get('customers/export/{format}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Customers\CustomerController::class,'export'])->where('format', 'excel|pdf')->name('customers.export');
Route::post('customers',[\App\Http\Controllers\Public\Business\Admin\Configurations\Customers\CustomerController::class,'store'])->name('customers.store');
Route::put('customers/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Customers\CustomerController::class,'update'])->whereNumber('record')->name('customers.update');
Route::delete('customers/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Customers\CustomerController::class,'destroy'])->whereNumber('record')->name('customers.destroy');
