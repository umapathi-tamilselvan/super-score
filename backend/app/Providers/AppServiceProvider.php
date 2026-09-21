<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\MatchSetupServiceInterface;
use App\Services\Contracts\OtpNotifierInterface;
use App\Services\Contracts\OtpServiceInterface;
use App\Services\Contracts\PlayerServiceInterface;
use App\Services\Contracts\PlayingXiServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\TossServiceInterface;
use App\Services\MailOtpNotifier;
use App\Services\MatchSetupService;
use App\Services\OtpService;
use App\Services\PlayerService;
use App\Services\PlayingXiService;
use App\Services\ProfileService;
use App\Services\Scoring\Contracts\DeliveryProcessorInterface;
use App\Services\Scoring\Contracts\InningsServiceInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use App\Services\Scoring\Contracts\StrikeRotationInterface;
use App\Services\Scoring\DeliveryProcessingService;
use App\Services\Scoring\InningsService;
use App\Services\Scoring\ScoreProjectionService;
use App\Services\Scoring\StrikeRotationService;
use App\Services\TeamService;
use App\Services\TossService;
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
        $this->app->bind(MatchSetupServiceInterface::class, MatchSetupService::class);
        $this->app->bind(PlayingXiServiceInterface::class, PlayingXiService::class);
        $this->app->bind(TossServiceInterface::class, TossService::class);
        $this->app->bind(ScoreProjectorInterface::class, ScoreProjectionService::class);
        $this->app->bind(StrikeRotationInterface::class, StrikeRotationService::class);
        $this->app->bind(DeliveryProcessorInterface::class, DeliveryProcessingService::class);
        $this->app->bind(InningsServiceInterface::class, InningsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
