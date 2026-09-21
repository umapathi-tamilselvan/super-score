<?php

namespace App\Services\Scoring\Dismissals;

use App\Enums\DismissalType;
use App\Services\Scoring\Contracts\DismissalHandlerInterface;

/**
 * Resolves the handler for a dismissal type. Adding a new dismissal
 * type means adding a case here and a new handler class — nothing
 * else in the scoring engine changes.
 */
class DismissalHandlerFactory
{
    public function forType(DismissalType $type): DismissalHandlerInterface
    {
        return match ($type) {
            DismissalType::Bowled => new BowledDismissalHandler,
            DismissalType::Caught => new CaughtDismissalHandler,
            DismissalType::Lbw => new LbwDismissalHandler,
            DismissalType::RunOut => new RunOutDismissalHandler,
            DismissalType::Stumped => new StumpedDismissalHandler,
            DismissalType::HitWicket => new HitWicketDismissalHandler,
            DismissalType::RetiredHurt => new RetiredHurtDismissalHandler,
            DismissalType::RetiredOut => new RetiredOutDismissalHandler,
            DismissalType::ObstructingField => new ObstructingFieldDismissalHandler,
        };
    }
}
