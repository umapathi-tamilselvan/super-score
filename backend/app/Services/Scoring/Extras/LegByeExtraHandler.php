<?php

namespace App\Services\Scoring\Extras;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * A legal delivery that came off the batter's body/pads rather than
 * the bat — scored identically to a bye, but recorded as a distinct
 * extras category.
 */
class LegByeExtraHandler implements ExtraHandlerInterface
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
