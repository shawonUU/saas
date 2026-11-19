<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\ReportController;
Route::get('finance/ledger/general-ledger',[ReportController::class,'ledger'])->name('reports.ledger');
Route::get('finance/financial-reports/cash-flow',[ReportController::class,'cash'])->name('reports.cash');
Route::get('sales/reports/sales-summary',[ReportController::class,'salesSummary'])->name('reports.sales');
Route::get('finance/ledger/journal-entries',[ReportController::class,'journals'])->name('reports.journals');
Route::get('finance/financial-reports/trial-balance',[ReportController::class,'trial'])->name('reports.trial');
Route::get('finance/financial-reports/profit-loss',[ReportController::class,'profit'])->name('reports.profit');
Route::get('finance/financial-reports/balance-sheet',[ReportController::class,'sheet'])->name('reports.sheet');
Route::get('finance/receivables/customer-dues',[ReportController::class,'dues'])->name('reports.dues');
Route::get('finance/payables/vendor-bills',[ReportController::class,'bills'])->name('reports.bills');
Route::get('ecommerce/online-orders/online-order-list',[ReportController::class,'sales'])->name('reports.online');
