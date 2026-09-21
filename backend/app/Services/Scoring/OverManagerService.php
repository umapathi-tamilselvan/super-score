<?php

namespace App\Services\Scoring;

use App\Models\Innings;
use App\Models\Over;
use App\Models\Player;
use InvalidArgumentException;

class OverManagerService
{
    public function startFirstOver(Innings $innings, Player $bowler): Over
    {
        return $innings->overs()->create(['over_number' => 1, 'bowler_id' => $bowler->id]);
    }

    /**
     * Starts the next over: rotates the strike (ends always swap after
     * an over, regardless of the last ball's run count) and enforces
     * that the same bowler can't bowl two overs in a row.
     *
     * @throws InvalidArgumentException when the current over isn't finished yet, or the bowler bowled the previous over.
     */
    public function startNextOver(Innings $innings, Player $bowler): Over
    {
        if (! $innings->isInProgress()) {
            throw new InvalidArgumentException('This innings has already ended.');
        }

        $previousOver = $innings->currentOver();

        if (! $previousOver || ! $previousOver->isComplete()) {
            throw new InvalidArgumentException('The current over is not complete yet.');
        }

        if ($previousOver->bowler_id === $bowler->id) {
            throw new InvalidArgumentException('The same bowler cannot bowl two overs in a row.');
        }

        $innings->update([
            'current_striker_id' => $innings->current_non_striker_id,
            'current_non_striker_id' => $innings->current_striker_id,
            'current_bowler_id' => $bowler->id,
        ]);

        return $innings->overs()->create([
            'over_number' => $previousOver->over_number + 1,
            'bowler_id' => $bowler->id,
        ]);
    }
}
