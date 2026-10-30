<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\R2FileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// FILE SYSTEM
Route::post('/images', [ImageController::class, 'store']);
Route::delete('/images/{filename}', [ImageController::class, 'destroy']);

// R2

Route::post('/r2/files', [R2FileController::class, 'store']);
Route::get('/r2/files/{name}', [R2FileController::class, 'download']);

Route::post('/order', [OrderController::class, 'store']);

// Notification
Route::post("/send-mail", [NotificationController::class, 'sendMail']);


require __DIR__.'/apis/product.php';
require __DIR__.'/apis/category.php';
require __DIR__.'/apis/stock-movement.php';
require __DIR__.'/apis/sale.php';
require __DIR__.'/apis/order.php';
require __DIR__.'/apis/sale-item.php';
require __DIR__.'/apis/payment.php';
require __DIR__.'/apis/user.php';
