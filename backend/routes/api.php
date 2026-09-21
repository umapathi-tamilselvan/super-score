<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeliveryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InningsController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\PlayingXiController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TossController;
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

            Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
            Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
            Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
            Route::put('/matches/{match}/teams', [MatchController::class, 'selectTeams'])->name('matches.teams.update');
            Route::put('/matches/{match}/playing-xi/{team}', [PlayingXiController::class, 'update'])->name('matches.playing-xi.update');
            Route::put('/matches/{match}/toss', [TossController::class, 'update'])->name('matches.toss.update');
            Route::get('/matches/{match}/live', [MatchController::class, 'live'])->name('matches.live');

            Route::post('/matches/{match}/innings', [InningsController::class, 'store'])->name('innings.store');
            Route::get('/innings/{innings}', [InningsController::class, 'show'])->name('innings.show');
            Route::post('/innings/{innings}/new-batter', [InningsController::class, 'selectNewBatter'])->name('innings.new-batter');
            Route::post('/innings/{innings}/next-over', [InningsController::class, 'startNextOver'])->name('innings.next-over');

            Route::get('/innings/{innings}/deliveries', [DeliveryController::class, 'history'])->name('innings.deliveries.index');
            Route::post('/innings/{innings}/deliveries', [DeliveryController::class, 'store'])->name('innings.deliveries.store');
            Route::post('/innings/{innings}/deliveries/undo', [DeliveryController::class, 'undo'])->name('innings.deliveries.undo');
            Route::put('/innings/{innings}/deliveries/last', [DeliveryController::class, 'updateLast'])->name('innings.deliveries.update-last');
        });
    });
});
