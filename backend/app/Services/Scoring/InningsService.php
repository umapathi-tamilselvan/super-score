<?php

namespace App\Services\Scoring;

use App\Enums\InningsStatus;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\Player;
use App\Models\Team;
use App\Services\Scoring\Contracts\InningsServiceInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use InvalidArgumentException;

class InningsService implements InningsServiceInterface
{
    public function __construct(
        private readonly OverManagerService $overManager,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function start(CricketMatch $match, Team $battingTeam, Player $striker, Player $nonStriker, Player $openingBowler): Innings
    {
        if ($striker->id === $nonStriker->id) {
            throw new InvalidArgumentException('The opening striker and non-striker must be different players.');
        }

        $previousInnings = $match->innings()->orderByDesc('innings_number')->first();

        if ($previousInnings && $previousInnings->isInProgress()) {
            throw new InvalidArgumentException('The current innings must be completed before starting the next one.');
        }

        $inningsNumber = $previousInnings ? $previousInnings->innings_number + 1 : 1;
        $target = null;

        if ($inningsNumber === 2 && $previousInnings) {
            $target = $this->scoreProjector->project($previousInnings)->teamRuns + 1;
        }

        $innings = $match->innings()->create([
            'team_id' => $battingTeam->id,
            'innings_number' => $inningsNumber,
            'target' => $target,
            'status' => InningsStatus::InProgress,
            'current_striker_id' => $striker->id,
            'current_non_striker_id' => $nonStriker->id,
            'current_bowler_id' => $openingBowler->id,
        ]);

        $this->overManager->startFirstOver($innings, $openingBowler);

        return $innings;
    }

    public function selectNewBatter(Innings $innings, Player $newBatter): void
    {
        if ($innings->current_striker_id && $innings->current_non_striker_id) {
            throw new InvalidArgumentException('There is no vacant batting slot right now.');
        }

        $otherBatterId = $innings->current_striker_id ?? $innings->current_non_striker_id;

        if ($newBatter->id === $otherBatterId) {
            throw new InvalidArgumentException('This player is already batting.');
        }

        $alreadyOut = $innings->deliveries()->where('dismissed_player_id', $newBatter->id)->exists();

        if ($alreadyOut) {
            throw new InvalidArgumentException('This player has already been dismissed in this innings.');
        }

        if ($innings->current_striker_id === null) {
            $innings->update(['current_striker_id' => $newBatter->id]);
        } else {
            $innings->update(['current_non_striker_id' => $newBatter->id]);
        }
    }
}
