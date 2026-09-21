<?php

namespace App\DataTransferObjects;

/**
 * What a specific dismissal type means for scoring purposes — computed
 * by a DismissalHandlerInterface implementation.
 */
final class DismissalOutcome
{
    public function __construct(
        public readonly bool $creditedToBowler,
        public readonly bool $requiresFielder,
        public readonly bool $allowedOnFreeHit,
    ) {}
}
