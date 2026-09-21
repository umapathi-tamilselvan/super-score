<?php

namespace App\DataTransferObjects;

use App\Models\Player;

final class BatterStat
{
    public function __construct(
        public readonly Player $player,
        public readonly int $runs,
        public readonly int $balls,
        public readonly int $fours,
        public readonly int $sixes,
        public readonly bool $isOut,
        public readonly ?string $dismissalDescription,
    ) {}

    public function strikeRate(): float
    {
        return $this->balls > 0 ? round($this->runs / $this->balls * 100, 2) : 0.0;
    }
}
