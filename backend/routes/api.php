<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TeamController;
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

            Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
            Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
            Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
            Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
            Route::post('/teams/{team}/players', [TeamController::class, 'addPlayer'])->name('teams.players.store');
            Route::delete('/teams/{team}/players/{player}', [TeamController::class, 'removePlayer'])->name('teams.players.destroy');
            Route::put('/teams/{team}/role', [TeamController::class, 'assignRole'])->name('teams.role');

            Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
            Route::get('/player-profile', [PlayerController::class, 'showOwn'])->name('player-profile.show');
            Route::put('/player-profile', [PlayerController::class, 'updateOwn'])->name('player-profile.update');
        });
    });
});
