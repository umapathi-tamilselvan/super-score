<?php

namespace App\DataTransferObjects;

/**
 * Everything about an innings' current state, derived entirely from
 * its ordered list of deliveries — never from independently mutated
 * counters. Editing a delivery and re-running the projection is the
 * whole recalculation story.
 */
final class InningsProjection
{
    /**
     * @param  array<int, BatterStat>  $batterStats  keyed by player id
     * @param  array<int, BowlerStat>  $bowlerStats  keyed by player id
     * @param  array<string, int>  $extras  keys: wide, no_ball, bye, leg_bye, penalty, total
     * @param  FallOfWicket[]  $fallOfWickets
     */
    public function __construct(
        public readonly int $teamRuns,
        public readonly int $wickets,
        public readonly int $legalBallsBowled,
        public readonly array $batterStats,
        public readonly array $bowlerStats,
        public readonly array $extras,
        public readonly array $fallOfWickets,
    ) {}

    public function oversDisplay(): string
    {
        return intdiv($this->legalBallsBowled, 6).'.'.($this->legalBallsBowled % 6);
    }

    public function runRate(): float
    {
        return $this->legalBallsBowled > 0
            ? round($this->teamRuns / ($this->legalBallsBowled / 6), 2)
            : 0.0;
    }
}
