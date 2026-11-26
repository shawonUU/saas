<?php
use Illuminate\Support\Facades\Route;

Route::get('dining-areas',[\App\Http\Controllers\Public\Business\Admin\Configurations\DiningAreas\DiningAreaController::class,'index'])->name('dining-areas.index');
Route::post('dining-areas',[\App\Http\Controllers\Public\Business\Admin\Configurations\DiningAreas\DiningAreaController::class,'store'])->name('dining-areas.store');
Route::put('dining-areas/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\DiningAreas\DiningAreaController::class,'update'])->whereNumber('record')->name('dining-areas.update');
Route::delete('dining-areas/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\DiningAreas\DiningAreaController::class,'destroy'])->whereNumber('record')->name('dining-areas.destroy');
