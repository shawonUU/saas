<?php
use Illuminate\Support\Facades\Route;

Route::get('reports/sales-summary',[\App\Http\Controllers\Public\Business\Admin\Sales\Reports\SalesSummary\SalesSummaryController::class,'salesSummary'])->name('reports.sales-summary.index');
