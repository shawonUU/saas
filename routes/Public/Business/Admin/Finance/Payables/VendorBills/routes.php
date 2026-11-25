<?php
use Illuminate\Support\Facades\Route;

Route::get('payables/vendor-bills',[\App\Http\Controllers\Public\Business\Admin\Finance\Payables\VendorBills\VendorBillsController::class,'bills'])->name('payables.vendor-bills.index');
