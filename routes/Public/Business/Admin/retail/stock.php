<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Inventory\StockController;
Route::get('inventory/stock/stock-report',[StockController::class,'report'])->name('stock.report');
Route::get('inventory/stock/stock-ledger',[StockController::class,'ledger'])->name('stock.ledger');
Route::get('inventory/stock/low-stock',[StockController::class,'low'])->name('stock.low');
Route::get('inventory/movements/stock-adjustment',[StockController::class,'adjustment'])->name('stock.adjustment');
Route::post('inventory/movements/stock-adjustment',[StockController::class,'adjust'])->name('stock.adjust');
