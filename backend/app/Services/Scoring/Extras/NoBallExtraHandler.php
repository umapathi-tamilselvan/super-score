<?php

namespace App\Services\Scoring\Extras;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * A no ball is illegal but still reaches the batter, so runs off the
 * bat count — plus the mandatory 1-run penalty, and any additional
 * runs run while the ball was live. It also earns the batter a free
 * hit on the next delivery. Simplification: unlike a true bye/leg-bye,
 * any additional runs run on a no ball are still charged to the
 * bowler here (common convention in club-level scoring), so nothing
 * needs to be tracked separately from the ball's total.
 */
class NoBallExtraHandler implements ExtraHandlerInterface
{
    private const PENALTY_RUNS = 1;

    public function apply(DeliveryInput $input): ExtraOutcome
    {
        return new ExtraOutcome(
            isLegalDelivery: false,
            teamRuns: self::PENALTY_RUNS + $input->runsOffBat + $input->extraRuns,
            batterRuns: $input->runsOffBat,
            triggersFreeHit: true,
        );
    }
}
