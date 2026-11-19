<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\GatewaySettlementController;
Route::get('finance/banking/bank-reconciliation',[GatewaySettlementController::class,'index'])->name('gateway_settlements.index');
Route::post('finance/banking/bank-reconciliation/{record}/settle',[GatewaySettlementController::class,'settle'])->whereNumber('record')->name('gateway_settlements.settle');
