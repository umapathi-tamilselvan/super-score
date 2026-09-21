<?php

namespace App\Services\Scoring\Extras;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * Penalty runs awarded for an infringement (e.g. fielding side
 * misconduct) — added to the team total, charged to neither the
 * batter nor the bowler, and don't affect legality of the delivery
 * it's recorded against.
 */
class PenaltyExtraHandler implements ExtraHandlerInterface
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
