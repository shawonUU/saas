<?php
use Illuminate\Support\Facades\Route;

Route::post('expenses/new-expense',[\App\Http\Controllers\Public\Business\Admin\Finance\Expenses\ExpenseController::class,'store'])->name('expenses.new-expense.store');
