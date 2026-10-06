<?php

use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::prefix('stock-movements')->group(function (): void {
    Route::get('/', [StockMovementController::class, 'index']);
    Route::get('/{id}', [StockMovementController::class, 'show']);
    Route::post('/', [StockMovementController::class, 'store']);
    Route::put('/{id}', [StockMovementController::class, 'update']);
    Route::delete('/{id}', [StockMovementController::class, 'destroy']);
});
