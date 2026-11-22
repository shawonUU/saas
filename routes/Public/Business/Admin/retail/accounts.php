<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Accounts\AccountController;
Route::get('finance/chart-of-accounts/account-list',[AccountController::class,'index'])->name('accounts.index');
Route::post('finance/chart-of-accounts/account-list',[AccountController::class,'store'])->name('accounts.store');
Route::put('finance/chart-of-accounts/account-list/{record}',[AccountController::class,'update'])->whereNumber('record')->name('accounts.update');
