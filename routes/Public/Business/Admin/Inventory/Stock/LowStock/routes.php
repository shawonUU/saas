<?php
use Illuminate\Support\Facades\Route;

Route::get('stock/low-stock',[\App\Http\Controllers\Public\Business\Admin\Inventory\Stock\LowStock\LowStockController::class,'low'])->name('stock.low-stock.index');
