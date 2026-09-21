<?php

namespace App\DataTransferObjects;

use App\Enums\DismissalType;
use App\Enums\ExtraType;

/**
 * The scorer's raw input for a single ball — everything the client
 * submits, before any cricket rules (extras, dismissals) are applied.
 */
final class DeliveryInput
{
    public function __construct(
        public readonly int $runsOffBat = 0,
        public readonly ?ExtraType $extraType = null,
        public readonly int $extraRuns = 0,
        public readonly bool $isWicket = false,
        public readonly ?DismissalType $dismissalType = null,
        public readonly ?int $dismissedPlayerId = null,
        public readonly ?int $fielderId = null,
    ) {}
}
