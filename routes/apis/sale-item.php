<?php

use App\Http\Controllers\SaleItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('sale-items')->group(function (): void {
    Route::get('/', [SaleItemController::class, 'index']);
    Route::get('/{id}', [SaleItemController::class, 'show']);
    Route::post('/', [SaleItemController::class, 'store']);
    Route::put('/{id}', [SaleItemController::class, 'update']);
    Route::delete('/{id}', [SaleItemController::class, 'destroy']);
});
