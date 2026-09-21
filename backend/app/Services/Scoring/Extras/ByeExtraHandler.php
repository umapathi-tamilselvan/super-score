<?php

namespace App\Services\Scoring\Extras;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * A legal delivery the batter missed entirely — runs are added to the
 * team total but not charged to the bowler or the batter.
 */
class ByeExtraHandler implements ExtraHandlerInterface
{
    public function apply(DeliveryInput $input): ExtraOutcome
    {
        return new ExtraOutcome(
            isLegalDelivery: true,
            teamRuns: $input->extraRuns,
            batterRuns: 0,
        );
    }
}
