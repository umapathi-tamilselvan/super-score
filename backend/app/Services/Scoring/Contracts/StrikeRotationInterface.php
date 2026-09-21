<?php

namespace App\Services\Scoring\Contracts;

use App\Models\Delivery;
use App\Models\Innings;

/**
 * Decides whether the striker and non-striker swap ends after a
 * (non-wicket) delivery, based on how many runs were actually run.
 */
interface StrikeRotationInterface
{
    public function applyAfterDelivery(Innings $innings, Delivery $delivery): void;
}
