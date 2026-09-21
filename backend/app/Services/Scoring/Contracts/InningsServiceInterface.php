<?php

namespace App\Services\Scoring\Contracts;

use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\Player;
use App\Models\Team;

/**
 * Owns innings lifecycle (start, new-batter selection) — a different
 * responsibility from DeliveryProcessorInterface's per-ball concern.
 * Not named in the original plan's file tree, but split out for the
 * same Single Responsibility reason the plan splits everything else.
 */
interface InningsServiceInterface
{
    /**
     * @throws \InvalidArgumentException when an innings for this match/team already exists, or the second innings is started before the first completes.
     */
    public function start(CricketMatch $match, Team $battingTeam, Player $striker, Player $nonStriker, Player $openingBowler): Innings;

    /**
     * Fill whichever batting slot a wicket just vacated.
     *
     * @throws \InvalidArgumentException when no slot is vacant, or the player is already out/batting.
     */
    public function selectNewBatter(Innings $innings, Player $newBatter): void;
}
