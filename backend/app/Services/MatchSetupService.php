<?php

namespace App\Services;

use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\MatchSetupServiceInterface;
use InvalidArgumentException;

class MatchSetupService implements MatchSetupServiceInterface
{
    public function create(User $organizer, array $data): CricketMatch
    {
        return $organizer->organizedMatches()->create([
            ...$data,
            'status' => MatchStatus::Scheduled,
        ]);
    }

    public function selectTeams(CricketMatch $match, Team $teamA, Team $teamB): void
    {
        if ($teamA->id === $teamB->id) {
            throw new InvalidArgumentException('Team A and Team B must be different teams.');
        }

        foreach ([$teamA, $teamB] as $team) {
            if ($team->players()->count() < config('cricket.playing_xi_size')) {
                throw new InvalidArgumentException(
                    "{$team->name} does not have enough registered players for this format."
                );
            }
        }

        $match->teams()->sync([
            $teamA->id => ['side' => MatchSide::TeamA->value],
            $teamB->id => ['side' => MatchSide::TeamB->value],
        ]);
    }
}
