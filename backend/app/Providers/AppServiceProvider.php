<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\OtpNotifierInterface;
use App\Services\Contracts\OtpServiceInterface;
use App\Services\Contracts\PlayerServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\MailOtpNotifier;
use App\Services\OtpService;
use App\Services\PlayerService;
use App\Services\ProfileService;
use App\Services\TeamService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OtpServiceInterface::class, OtpService::class);
        $this->app->bind(OtpNotifierInterface::class, MailOtpNotifier::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(PlayerServiceInterface::class, PlayerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
