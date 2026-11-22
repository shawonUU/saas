<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Accounts\ManualJournalController;
Route::get('finance/ledger/journal-entries/manual',[ManualJournalController::class,'index'])->name('manual_journals.index');
Route::post('finance/ledger/journal-entries/manual',[ManualJournalController::class,'store'])->name('manual_journals.store');
