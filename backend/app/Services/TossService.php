<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\TossDecision;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Toss;
use App\Services\Contracts\TossServiceInterface;

class TossService implements TossServiceInterface
{
    public function record(CricketMatch $match, Team $winner, TossDecision $decision): Toss
    {
        $toss = Toss::updateOrCreate(
            ['match_id' => $match->id],
            ['winner_team_id' => $winner->id, 'decision' => $decision],
        );

        $match->update(['status' => MatchStatus::TossCompleted]);

        return $toss;
    }
}
