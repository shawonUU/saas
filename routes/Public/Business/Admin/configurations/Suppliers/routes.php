<?php
use Illuminate\Support\Facades\Route;

Route::get('suppliers',[\App\Http\Controllers\Public\Business\Admin\Configurations\Suppliers\SupplierController::class,'index'])->name('suppliers.index');
Route::post('suppliers',[\App\Http\Controllers\Public\Business\Admin\Configurations\Suppliers\SupplierController::class,'store'])->name('suppliers.store');
Route::put('suppliers/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Suppliers\SupplierController::class,'update'])->whereNumber('record')->name('suppliers.update');
Route::delete('suppliers/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Suppliers\SupplierController::class,'destroy'])->whereNumber('record')->name('suppliers.destroy');
