<?php

namespace App\Services\Scoring\Dismissals;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\DismissalOutcome;
use App\Services\Scoring\Contracts\DismissalHandlerInterface;

class BowledDismissalHandler implements DismissalHandlerInterface
{
    public function apply(DeliveryInput $input): DismissalOutcome
    {
        return new DismissalOutcome(creditedToBowler: true, requiresFielder: false, allowedOnFreeHit: false);
    }
}
