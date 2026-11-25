<?php
use Illuminate\Support\Facades\Route;

Route::get('taxes',[\App\Http\Controllers\Public\Business\Admin\Configurations\Taxes\TaxController::class,'index'])->name('taxes.index');
Route::post('taxes',[\App\Http\Controllers\Public\Business\Admin\Configurations\Taxes\TaxController::class,'store'])->name('taxes.store');
Route::put('taxes/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Taxes\TaxController::class,'update'])->whereNumber('record')->name('taxes.update');
Route::delete('taxes/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Taxes\TaxController::class,'destroy'])->whereNumber('record')->name('taxes.destroy');
