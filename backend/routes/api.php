<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', HealthController::class)->name('health');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
        Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');

        Route::middleware('otp.verified')->group(function () {
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        });
    });
});
