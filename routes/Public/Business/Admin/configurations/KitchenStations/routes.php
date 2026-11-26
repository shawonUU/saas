<?php
use Illuminate\Support\Facades\Route;

Route::get('kitchen-stations',[\App\Http\Controllers\Public\Business\Admin\Configurations\KitchenStations\KitchenStationController::class,'index'])->name('kitchen-stations.index');
Route::post('kitchen-stations',[\App\Http\Controllers\Public\Business\Admin\Configurations\KitchenStations\KitchenStationController::class,'store'])->name('kitchen-stations.store');
Route::put('kitchen-stations/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\KitchenStations\KitchenStationController::class,'update'])->whereNumber('record')->name('kitchen-stations.update');
Route::delete('kitchen-stations/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\KitchenStations\KitchenStationController::class,'destroy'])->whereNumber('record')->name('kitchen-stations.destroy');
