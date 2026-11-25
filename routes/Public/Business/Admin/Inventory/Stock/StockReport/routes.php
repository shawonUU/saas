<?php
use Illuminate\Support\Facades\Route;

Route::get('stock/stock-report',[\App\Http\Controllers\Public\Business\Admin\Inventory\Stock\StockReport\StockReportController::class,'report'])->name('stock.stock-report.index');
