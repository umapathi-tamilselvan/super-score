<?php

namespace App\DataTransferObjects;

use App\Models\Player;

final class BowlerStat
{
    public function __construct(
        public readonly Player $player,
        public readonly int $legalBalls,
        public readonly int $runsConceded,
        public readonly int $wickets,
    ) {}

    public function oversDisplay(): string
    {
        return intdiv($this->legalBalls, 6).'.'.($this->legalBalls % 6);
    }

    public function economy(): float
    {
        return $this->legalBalls > 0 ? round($this->runsConceded / ($this->legalBalls / 6), 2) : 0.0;
    }
}
