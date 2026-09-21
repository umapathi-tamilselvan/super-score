<?php

namespace App\Services\Scoring\Dismissals;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\DismissalOutcome;
use App\Services\Scoring\Contracts\DismissalHandlerInterface;

/**
 * Retiring hurt is the batter's own choice, not something the
 * delivery caused — so the free-hit protection (which only shields
 * against dismissals the ball itself produces) doesn't apply.
 */
class RetiredHurtDismissalHandler implements DismissalHandlerInterface
{
    public function apply(DeliveryInput $input): DismissalOutcome
    {
        return new DismissalOutcome(creditedToBowler: false, requiresFielder: false, allowedOnFreeHit: true);
    }
}
