<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Authorization\Permissions\PermissionController;
Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
