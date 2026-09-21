<?php

namespace App\Services\Scoring\Dismissals;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\DismissalOutcome;
use App\Services\Scoring\Contracts\DismissalHandlerInterface;

class RetiredOutDismissalHandler implements DismissalHandlerInterface
{
    public function apply(DeliveryInput $input): DismissalOutcome
    {
        return new DismissalOutcome(creditedToBowler: false, requiresFielder: false, allowedOnFreeHit: true);
    }
}
