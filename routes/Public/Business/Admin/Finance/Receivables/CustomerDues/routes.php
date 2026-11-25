<?php
use Illuminate\Support\Facades\Route;

Route::get('receivables/customer-dues',[\App\Http\Controllers\Public\Business\Admin\Finance\Receivables\CustomerDues\CustomerDuesController::class,'dues'])->name('receivables.customer-dues.index');
