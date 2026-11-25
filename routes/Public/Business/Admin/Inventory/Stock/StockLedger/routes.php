<?php
use Illuminate\Support\Facades\Route;

Route::get('stock/stock-ledger',[\App\Http\Controllers\Public\Business\Admin\Inventory\Stock\StockLedger\StockLedgerController::class,'ledger'])->name('stock.stock-ledger.index');
