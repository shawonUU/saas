<?php
use Illuminate\Support\Facades\Route;

Route::get('ledger/general-ledger',[\App\Http\Controllers\Public\Business\Admin\Finance\Ledger\GeneralLedger\GeneralLedgerController::class,'ledger'])->name('ledger.general-ledger.index');
