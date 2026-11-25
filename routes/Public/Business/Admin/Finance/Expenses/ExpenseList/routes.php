<?php
use Illuminate\Support\Facades\Route;

Route::get('expenses/expense-list',[\App\Http\Controllers\Public\Business\Admin\Finance\Expenses\ExpenseController::class,'index'])->name('expenses.expense-list.index');
