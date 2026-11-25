<?php
use Illuminate\Support\Facades\Route;

Route::get('financial-reports/balance-sheet',[\App\Http\Controllers\Public\Business\Admin\Finance\FinancialReports\FinancialReportController::class,'sheet'])->name('financial-reports.balance-sheet.index');
