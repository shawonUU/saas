<?php
use Illuminate\Support\Facades\Route;

Route::get('movements/stock-adjustment',[\App\Http\Controllers\Public\Business\Admin\Inventory\Movements\StockAdjustment\StockAdjustmentController::class,'adjustment'])->name('movements.stock-adjustment.index');
Route::post('movements/stock-adjustment',[\App\Http\Controllers\Public\Business\Admin\Inventory\Movements\StockAdjustment\StockAdjustmentController::class,'adjust'])->name('movements.stock-adjustment.store');
