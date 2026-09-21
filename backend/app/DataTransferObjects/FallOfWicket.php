<?php

namespace App\DataTransferObjects;

use App\Models\Player;

final class FallOfWicket
{
    public function __construct(
        public readonly int $wicketNumber,
        public readonly int $teamScoreAtFall,
        public readonly Player $playerOut,
        public readonly string $oversDisplay,
    ) {}
}
