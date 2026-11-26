<?php
use Illuminate\Support\Facades\Route;

Route::get('tables',[\App\Http\Controllers\Public\Business\Admin\Configurations\Tables\DiningTableController::class,'index'])->name('tables.index');
Route::post('tables',[\App\Http\Controllers\Public\Business\Admin\Configurations\Tables\DiningTableController::class,'store'])->name('tables.store');
Route::put('tables/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Tables\DiningTableController::class,'update'])->whereNumber('record')->name('tables.update');
Route::delete('tables/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Tables\DiningTableController::class,'destroy'])->whereNumber('record')->name('tables.destroy');
