<?php

use App\Http\Controllers\Api\MountainController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::post('/users', [UserController::class, 'store']); // Create User/Admin
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete
});

Route::apiResource('mountains', MountainController::class);
Route::apiResource('reservations', ReservationController::class);
