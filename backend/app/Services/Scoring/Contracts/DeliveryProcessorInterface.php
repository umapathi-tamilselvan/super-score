<?php

namespace App\Services\Scoring\Contracts;

use App\DataTransferObjects\DeliveryInput;
use App\Models\Delivery;
use App\Models\Innings;

/**
 * Records one ball against an innings: identifies striker/non-striker/
 * bowler from the innings' current state, delegates run/extra/wicket
 * handling to the right collaborator, persists the Delivery, updates
 * strike, and checks over/innings completion. Never branches on extra
 * or dismissal type itself — that's what the handler factories are for.
 */
interface DeliveryProcessorInterface
{
    /**
     * @throws \InvalidArgumentException when the delivery is invalid for the innings' current state.
     */
    public function record(Innings $innings, DeliveryInput $input): Delivery;
}
