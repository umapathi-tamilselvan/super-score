<?php

namespace App\Services\Scoring\Contracts;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;

/**
 * Computes what a specific extra type means for a ball: whether it
 * counts toward the over, how the runs split between team/batter/
 * bowler, and whether it triggers a free hit. DeliveryProcessingService
 * never branches on extra type itself — it asks the handler.
 */
interface ExtraHandlerInterface
{
    public function apply(DeliveryInput $input): ExtraOutcome;
}
