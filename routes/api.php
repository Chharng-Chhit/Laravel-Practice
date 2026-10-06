<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__.'/apis/product.php';
require __DIR__.'/apis/category.php';
require __DIR__.'/apis/stock-movement.php';
require __DIR__.'/apis/sale.php';
require __DIR__.'/apis/order.php';
require __DIR__.'/apis/sale-item.php';
require __DIR__.'/apis/payment.php';
require __DIR__.'/apis/user.php';
