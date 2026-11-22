<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Accounts\AccountGroupController;
Route::get('finance/chart-of-accounts/account-groups',[AccountGroupController::class,'index'])->name('account_groups.index');
