<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeliveryController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InningsController;
use App\Http\Controllers\Web\MatchController;
use App\Http\Controllers\Web\PlayerController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');

    Route::middleware('otp.verified')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::post('/teams/{team}/players', [TeamController::class, 'addPlayer'])->name('teams.players.store');
        Route::delete('/teams/{team}/players/{player}', [TeamController::class, 'removePlayer'])->name('teams.players.destroy');
        Route::put('/teams/{team}/role', [TeamController::class, 'assignRole'])->name('teams.role');

        Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
        Route::get('/player-profile', [PlayerController::class, 'editOwn'])->name('player-profile.edit');
        Route::put('/player-profile', [PlayerController::class, 'updateOwn'])->name('player-profile.update');

        Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
        Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
        Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
        Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
        Route::get('/matches/{match}/teams', [MatchController::class, 'editTeams'])->name('matches.teams.edit');
        Route::put('/matches/{match}/teams', [MatchController::class, 'updateTeams'])->name('matches.teams.update');
        Route::get('/matches/{match}/playing-xi/{team}', [MatchController::class, 'editPlayingXi'])->name('matches.playing-xi.edit');
        Route::put('/matches/{match}/playing-xi/{team}', [MatchController::class, 'updatePlayingXi'])->name('matches.playing-xi.update');
        Route::get('/matches/{match}/toss', [MatchController::class, 'editToss'])->name('matches.toss.edit');
        Route::put('/matches/{match}/toss', [MatchController::class, 'updateToss'])->name('matches.toss.update');

        Route::get('/matches/{match}/start-innings', [InningsController::class, 'create'])->name('innings.create');
        Route::post('/matches/{match}/start-innings', [InningsController::class, 'store'])->name('innings.store');
        Route::get('/innings/{innings}/live', [InningsController::class, 'live'])->name('innings.live');
        Route::get('/innings/{innings}', [InningsController::class, 'show'])->name('innings.show');
        Route::post('/innings/{innings}/new-batter', [InningsController::class, 'selectNewBatter'])->name('innings.new-batter');
        Route::post('/innings/{innings}/next-over', [InningsController::class, 'startNextOver'])->name('innings.next-over');

        Route::get('/innings/{innings}/deliveries', [DeliveryController::class, 'history'])->name('innings.deliveries.index');
        Route::post('/innings/{innings}/deliveries', [DeliveryController::class, 'store'])->name('innings.deliveries.store');
        Route::post('/innings/{innings}/deliveries/undo', [DeliveryController::class, 'undo'])->name('innings.deliveries.undo');
        Route::put('/innings/{innings}/deliveries/last', [DeliveryController::class, 'updateLast'])->name('innings.deliveries.update-last');
    });
});
