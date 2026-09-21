<?php

namespace App\Events;

use App\Models\Innings;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when an innings ends (all out or overs completed). Not
 * broadcast yet — Phase 6 wires this to Reverb for public live scoring.
 */
class InningsCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Innings $innings,
    ) {}
}
