<?php
use Illuminate\Support\Facades\Route;

Route::get('chart-of-accounts/account-groups',[\App\Http\Controllers\Public\Business\Admin\Finance\ChartOfAccounts\AccountGroups\AccountGroupController::class,'index'])->name('chart-of-accounts.account-groups.index');
