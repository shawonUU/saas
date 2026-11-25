<?php
use Illuminate\Support\Facades\Route;

Route::get('currencies',[\App\Http\Controllers\Public\Business\Admin\Configurations\Currencies\CurrencyController::class,'index'])->name('currencies.index');
Route::post('currencies',[\App\Http\Controllers\Public\Business\Admin\Configurations\Currencies\CurrencyController::class,'store'])->name('currencies.store');
Route::put('currencies/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Currencies\CurrencyController::class,'update'])->whereNumber('record')->name('currencies.update');
Route::delete('currencies/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Currencies\CurrencyController::class,'destroy'])->whereNumber('record')->name('currencies.destroy');
