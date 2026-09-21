<?php

namespace App\Services\Scoring\Extras;

use App\Enums\ExtraType;
use App\Services\Scoring\Contracts\ExtraHandlerInterface;

/**
 * Resolves the handler for an extra type. Adding a new extra type
 * means adding a case here and a new handler class — nothing else in
 * the scoring engine changes.
 */
class ExtraHandlerFactory
{
    public function forType(ExtraType $type): ExtraHandlerInterface
    {
        return match ($type) {
            ExtraType::Wide => new WideExtraHandler,
            ExtraType::NoBall => new NoBallExtraHandler,
            ExtraType::Bye => new ByeExtraHandler,
            ExtraType::LegBye => new LegByeExtraHandler,
            ExtraType::Penalty => new PenaltyExtraHandler,
        };
    }
}
