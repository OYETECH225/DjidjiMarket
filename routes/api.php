<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\VendorListingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
});

Route::get('/vendors', [VendorController::class, 'index']);
Route::get('/vendors/{slug}', [VendorController::class, 'show']);
Route::get('/vendors/{vendor}/listings', [VendorController::class, 'listings']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('vendor')->middleware('role:vendor')->group(function () {
        Route::post('/profile', [VendorController::class, 'storeProfile']);
        Route::get('/me', [VendorController::class, 'me']);
        Route::patch('/me', [VendorController::class, 'updateMe']);
        Route::get('/orders', [VendorController::class, 'myOrders']);
        Route::get('/listings', [VendorListingController::class, 'index']);
        Route::post('/listings', [VendorListingController::class, 'store']);
        Route::put('/listings/{listing}', [VendorListingController::class, 'update']);
        Route::delete('/listings/{listing}', [VendorListingController::class, 'destroy']);
    });

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/confirm-receipt', [OrderController::class, 'confirmReceipt']);

    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);

    Route::prefix('courier')->middleware('role:courier')->group(function () {
        Route::post('/profile', [CourierController::class, 'storeProfile']);
        Route::get('/me', [CourierController::class, 'me']);
        Route::get('/orders', [CourierController::class, 'myOrders']);
        Route::post('/availability', [CourierController::class, 'updateAvailability']);
        Route::get('/orders/available', [CourierController::class, 'available']);
        Route::post('/orders/{order}/accept', [CourierController::class, 'accept']);
        Route::post('/orders/{order}/status', [CourierController::class, 'updateStatus']);
    });
});

// Public: called by the mobile money aggregator, authenticated via shared secret header.
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
