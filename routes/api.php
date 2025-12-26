<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\MountainController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\InformasiController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers', [UserController::class, 'getCustomers']);
    Route::get('/admins', [UserController::class, 'getAdmins']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

Route::apiResource('mountains', MountainController::class);
Route::get('reservations/all', [ReservationController::class, 'all']);
Route::apiResource('reservations', ReservationController::class);
Route::apiResource('checkins', CheckinController::class);
Route::get('reservations/{id}/checkin', [CheckinController::class, 'getByReservation']);
Route::apiResource('checkouts', CheckoutController::class);
Route::get('checkins/{id}/checkout', [CheckoutController::class, 'getByCheckin']);
Route::get('reservations/{id}/checkout', [CheckoutController::class, 'getByReservation']);
Route::apiResource('histories', HistoryController::class);
Route::get('checkouts/{id}/history', [HistoryController::class, 'getByCheckout']);
Route::get('reservations/{id}/history', [HistoryController::class, 'getByReservation']);
Route::post('checkouts/{id}/create-history', [HistoryController::class, 'createFromCheckout']);
Route::apiResource('laporans', LaporanController::class);
Route::apiResource('informasi', InformasiController::class);
Route::post('histories/checkout/{id}', [HistoryController::class, 'createFromCheckout']);