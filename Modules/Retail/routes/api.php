<?php
use Illuminate\Support\Facades\Route;
use Modules\Retail\App\Http\Controllers\RetailController;
Route::get('api/retail/storefront/{slug}',[RetailController::class,'storefront'])->where('slug','[a-z0-9-]+');
Route::middleware(['auth:sanctum','auth.domain'])->prefix('api/retail/admin')->group(function(){
 Route::get('catalog',[RetailController::class,'catalog']);
 Route::post('categories',[RetailController::class,'category']);
 Route::post('products',[RetailController::class,'product']);
 Route::delete('products/{product}',[RetailController::class,'deleteProduct'])->whereNumber('product');
});
