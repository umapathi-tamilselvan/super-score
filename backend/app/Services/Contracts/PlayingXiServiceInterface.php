<?php

namespace App\Services\Contracts;

use App\Models\CricketMatch;
use App\Models\Team;

interface PlayingXiServiceInterface
{
    /**
     * Replace a team's playing XI for this match: exactly
     * config('cricket.playing_xi_size') players from the team's squad,
     * with a captain and wicket keeper (and optional vice-captain)
     * chosen from among them.
     *
     * @param  int[]  $playerIds
     *
     * @throws \InvalidArgumentException when the count is wrong, a player isn't in the squad, or captain/VC/WK isn't one of the selected players.
     */
    public function selectXi(
        CricketMatch $match,
        Team $team,
        array $playerIds,
        int $captainPlayerId,
        int $wicketKeeperPlayerId,
        ?int $viceCaptainPlayerId = null,
    ): void;
}
