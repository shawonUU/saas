<?php
use App\Http\Controllers\Public\Business\Admin\Retail\ExpenseController;
Route::get('finance/expenses/expense-list',[ExpenseController::class,'index'])->name('expenses.index');
Route::post('finance/expenses/new-expense',[ExpenseController::class,'store'])->name('expenses.store');
