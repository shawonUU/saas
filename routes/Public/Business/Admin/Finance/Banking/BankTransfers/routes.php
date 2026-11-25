<?php
use Illuminate\Support\Facades\Route;

Route::get('banking/bank-transfers',[\App\Http\Controllers\Public\Business\Admin\Finance\Banking\BankTransfers\BankTransferController::class,'index'])->name('banking.bank-transfers.index');
Route::post('banking/bank-transfers',[\App\Http\Controllers\Public\Business\Admin\Finance\Banking\BankTransfers\BankTransferController::class,'store'])->name('banking.bank-transfers.store');
