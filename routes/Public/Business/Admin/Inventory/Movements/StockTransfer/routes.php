<?php
use Illuminate\Support\Facades\Route;

Route::get('movements/stock-transfer',[\App\Http\Controllers\Public\Business\Admin\Inventory\Movements\StockTransfer\StockTransferController::class,'transfers'])->name('movements.stock-transfer.index');
Route::post('movements/stock-transfer',[\App\Http\Controllers\Public\Business\Admin\Inventory\Movements\StockTransfer\StockTransferController::class,'transfer'])->name('movements.stock-transfer.store');
