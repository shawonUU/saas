<?php

use App\Http\Controllers\Public\Business\Admin\Restaurant\Dining\TableMap\TableMapController;
use Illuminate\Support\Facades\Route;

Route::get('dining/table-map', [TableMapController::class, 'index'])->name('dining.table-map.index');
