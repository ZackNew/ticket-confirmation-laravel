<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return 'API';
});

// Payment Routes
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments', [PaymentController::class, 'index'])->middleware('auth:sanctum');
Route::post('/payment/by-ids', [PaymentController::class, 'getByIds'])->middleware('auth:sanctum');
Route::put('/payment/approve/{id}', [PaymentController::class, 'approveRequest'])->middleware('auth:sanctum');
Route::put('/payment/resolve/{id}', [PaymentController::class, 'resolveDuplicates'])->middleware('auth:sanctum');
Route::put('/payment/reject/{id}', [PaymentController::class, 'rejectRequest'])->middleware('auth:sanctum');
Route::get('/payments/download', [PaymentController::class, 'downloadPayments'])->middleware('auth:sanctum');

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); 