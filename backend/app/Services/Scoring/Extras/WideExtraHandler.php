<?php

namespace App\Services\Scoring\Extras;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * A wide never reaches the batter, so it can't be scored off the bat —
 * it doesn't count as a legal delivery, and every run is charged to
 * the bowler.
 */
class WideExtraHandler implements ExtraHandlerInterface
{
    public function apply(DeliveryInput $input): ExtraOutcome
    {
        $runs = max(1, $input->extraRuns);

        return new ExtraOutcome(
            isLegalDelivery: false,
            teamRuns: $runs,
            batterRuns: 0,
        );
    }
}
