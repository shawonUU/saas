<?php
use Illuminate\Support\Facades\Route;

Route::get('ledger/journal-entries/manual',[\App\Http\Controllers\Public\Business\Admin\Finance\Ledger\JournalEntries\ManualJournalController::class,'index'])->name('ledger.journal-entries.manual.index');
Route::post('ledger/journal-entries/manual',[\App\Http\Controllers\Public\Business\Admin\Finance\Ledger\JournalEntries\ManualJournalController::class,'store'])->name('ledger.journal-entries.manual.store');
Route::get('ledger/journal-entries',[\App\Http\Controllers\Public\Business\Admin\Finance\Ledger\JournalEntries\JournalReportController::class,'journals'])->name('ledger.journal-entries.index');
