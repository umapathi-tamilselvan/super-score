<?php

namespace App\Services\Contracts;

use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\User;

interface MatchSetupServiceInterface
{
    /**
     * @param  array{name: string, format: string, overs: int, date: string, time?: string, venue?: string}  $data
     */
    public function create(User $organizer, array $data): CricketMatch;

    /**
     * Select the two competing teams for a match. Both teams must have
     * at least a full playing XI's worth of players in their squad
     * (§11 validation rule).
     *
     * @throws \InvalidArgumentException when the teams are the same, or either lacks enough players.
     */
    public function selectTeams(CricketMatch $match, Team $teamA, Team $teamB): void;
}
