<?php

use App\Http\Controllers\Public\Business\Admin\Restaurant\Reports\FoodCostReport\FoodCostReportController;
use Illuminate\Support\Facades\Route;

Route::get('reports/food-cost-report', [FoodCostReportController::class, 'index'])->name('reports.food-cost-report.index');
