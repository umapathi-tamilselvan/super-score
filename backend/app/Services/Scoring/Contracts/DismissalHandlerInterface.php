<?php

namespace App\Services\Scoring\Contracts;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\DismissalOutcome;

/**
 * Computes what a specific dismissal type means for scoring: whether
 * it's credited to the bowler's figures, whether a fielder must be
 * recorded, and whether it's allowed to stand on a free hit.
 * DeliveryProcessingService never branches on dismissal type itself —
 * it asks the handler.
 */
interface DismissalHandlerInterface
{
    public function apply(DeliveryInput $input): DismissalOutcome;
}
