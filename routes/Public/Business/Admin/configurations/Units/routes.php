<?php
use Illuminate\Support\Facades\Route;

Route::get('units',[\App\Http\Controllers\Public\Business\Admin\Configurations\Units\UnitController::class,'index'])->name('units.index');
Route::post('units',[\App\Http\Controllers\Public\Business\Admin\Configurations\Units\UnitController::class,'store'])->name('units.store');
Route::put('units/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Units\UnitController::class,'update'])->whereNumber('record')->name('units.update');
Route::delete('units/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Units\UnitController::class,'destroy'])->whereNumber('record')->name('units.destroy');
