<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Authorization\Roles\RoleController;
Route::get('roles',[RoleController::class,'index'])->name('roles.index');
Route::post('roles',[RoleController::class,'store'])->name('roles.store');
Route::put('roles/{record}',[RoleController::class,'update'])->whereNumber('record')->name('roles.update');
Route::delete('roles/{record}',[RoleController::class,'destroy'])->whereNumber('record')->name('roles.destroy');
