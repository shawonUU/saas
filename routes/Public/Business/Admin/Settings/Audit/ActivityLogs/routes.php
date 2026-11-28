<?php

use App\Http\Controllers\Public\Business\Admin\Settings\Audit\ActivityLogs\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('audit/activity-logs', [ActivityLogController::class, 'index'])->name('audit.activity-logs.index');
Route::get('audit/activity-logs/record', [ActivityLogController::class, 'record'])->name('audit.activity-logs.record');
