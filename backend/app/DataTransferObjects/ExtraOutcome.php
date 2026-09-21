<?php

namespace App\DataTransferObjects;

/**
 * What a specific extra type (wide/no-ball/bye/leg-bye/penalty) means
 * for this ball — computed by an ExtraHandlerInterface implementation,
 * never branched on by the caller. Runs charged to the bowler aren't
 * captured here: they're a pure function of the persisted extra_type
 * (byes/leg-byes/penalty aren't charged, everything else is), so
 * ScoreProjectionService derives them directly rather than needing a
 * separate stored value.
 */
final class ExtraOutcome
{
    public function __construct(
        public readonly bool $isLegalDelivery,
        public readonly int $teamRuns,
        public readonly int $batterRuns,
        public readonly bool $triggersFreeHit = false,
    ) {}
}
