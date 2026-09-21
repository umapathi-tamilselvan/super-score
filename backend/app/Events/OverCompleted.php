<?php

namespace App\Events;

use App\Models\Over;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when an over's sixth legal delivery is recorded. Not
 * broadcast yet — Phase 6 wires this to Reverb for public live scoring.
 */
class OverCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Over $over,
    ) {}
}
