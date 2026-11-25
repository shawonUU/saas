<?php
use Illuminate\Support\Facades\Route;

Route::get('financial-reports/trial-balance',[\App\Http\Controllers\Public\Business\Admin\Finance\FinancialReports\FinancialReportController::class,'trial'])->name('financial-reports.trial-balance.index');
