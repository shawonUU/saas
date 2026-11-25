<?php
use Illuminate\Support\Facades\Route;

Route::get('financial-reports/cash-flow',[\App\Http\Controllers\Public\Business\Admin\Finance\FinancialReports\FinancialReportController::class,'cash'])->name('financial-reports.cash-flow.index');
