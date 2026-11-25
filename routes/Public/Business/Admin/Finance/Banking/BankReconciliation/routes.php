<?php
use Illuminate\Support\Facades\Route;

Route::get('banking/bank-reconciliation',[\App\Http\Controllers\Public\Business\Admin\Finance\Banking\BankReconciliation\GatewaySettlementController::class,'index'])->name('banking.bank-reconciliation.index');
Route::post('banking/bank-reconciliation/{record}/settle',[\App\Http\Controllers\Public\Business\Admin\Finance\Banking\BankReconciliation\GatewaySettlementController::class,'settle'])->whereNumber('record')->name('banking.bank-reconciliation.settle');
