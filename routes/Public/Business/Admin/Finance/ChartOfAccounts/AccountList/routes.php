<?php
use Illuminate\Support\Facades\Route;

Route::get('chart-of-accounts/account-list',[\App\Http\Controllers\Public\Business\Admin\Finance\ChartOfAccounts\AccountList\AccountController::class,'index'])->name('chart-of-accounts.account-list.index');
Route::post('chart-of-accounts/account-list',[\App\Http\Controllers\Public\Business\Admin\Finance\ChartOfAccounts\AccountList\AccountController::class,'store'])->name('chart-of-accounts.account-list.store');
Route::put('chart-of-accounts/account-list/{record}',[\App\Http\Controllers\Public\Business\Admin\Finance\ChartOfAccounts\AccountList\AccountController::class,'update'])->whereNumber('record')->name('chart-of-accounts.account-list.update');
