<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\BankTransferController;
Route::get('finance/banking/bank-transfers',[BankTransferController::class,'index'])->name('bank_transfers.index');
Route::post('finance/banking/bank-transfers',[BankTransferController::class,'store'])->name('bank_transfers.store');
