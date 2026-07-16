<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FuelProductController;
use App\Http\Controllers\Api\FuelSupplierController;
use App\Http\Controllers\Api\MovementController;
use App\Http\Controllers\Api\NfeLinkController;
use App\Http\Controllers\Api\RefuelingController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);

    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/current', [AccountController::class, 'current']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::post('/accounts/{account}/select', [AccountController::class, 'select']);

    // Global catalogs — not scoped to a ledger account.
    Route::apiResource('categories', CategoryController::class);
    Route::get('/vehicles/{vehicle}/efficiency', [VehicleController::class, 'efficiency']);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('fuel-suppliers', FuelSupplierController::class);
    Route::apiResource('fuel-products', FuelProductController::class);
    Route::apiResource('nfe-links', NfeLinkController::class);
    Route::apiResource('refuelings', RefuelingController::class);

    // Scoped to the account selected via /accounts/{account}/select.
    Route::middleware('account.selected')->group(function () {
        Route::apiResource('books', BookController::class);
        Route::get('/movements/summary', [MovementController::class, 'summary']);
        Route::apiResource('movements', MovementController::class);
    });
});
