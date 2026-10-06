<?php

use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('sales')->group(function (): void {
    Route::get('/', [SaleController::class, 'index']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::post('/', [SaleController::class, 'store']);
    Route::put('/{id}', [SaleController::class, 'update']);
    Route::delete('/{id}', [SaleController::class, 'destroy']);
});
