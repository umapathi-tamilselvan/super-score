<?php

namespace App\Services\Scoring\Contracts;

use App\DataTransferObjects\InningsProjection;
use App\Models\Innings;

/**
 * Turns an innings' ordered delivery list into every derived number
 * the app needs (score, batter/bowler figures, extras, fall of
 * wickets) — the single place this math happens, so editing a
 * delivery and calling this again is the entire "recalculation" story.
 */
interface ScoreProjectorInterface
{
    public function project(Innings $innings): InningsProjection;
}
