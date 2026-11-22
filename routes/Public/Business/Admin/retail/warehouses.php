<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Inventory\WarehouseController;
Route::get('settings/locations/warehouses',[WarehouseController::class,'index'])->name('warehouses.index');
Route::post('settings/locations/warehouses',[WarehouseController::class,'store'])->name('warehouses.store');
Route::get('inventory/movements/stock-transfer',[WarehouseController::class,'transfers'])->name('warehouses.transfers');
Route::post('inventory/movements/stock-transfer',[WarehouseController::class,'transfer'])->name('warehouses.transfer');
