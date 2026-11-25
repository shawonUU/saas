<?php
use Illuminate\Support\Facades\Route;

Route::get('financial-reports/profit-loss',[\App\Http\Controllers\Public\Business\Admin\Finance\FinancialReports\FinancialReportController::class,'profit'])->name('financial-reports.profit-loss.index');
