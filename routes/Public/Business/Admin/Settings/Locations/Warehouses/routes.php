<?php
use Illuminate\Support\Facades\Route;

Route::get('locations/warehouses',[\App\Http\Controllers\Public\Business\Admin\Settings\Locations\Warehouses\WarehouseController::class,'index'])->name('locations.warehouses.index');
Route::post('locations/warehouses',[\App\Http\Controllers\Public\Business\Admin\Settings\Locations\Warehouses\WarehouseController::class,'store'])->name('locations.warehouses.store');
