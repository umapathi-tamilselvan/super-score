<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a delivery results in a wicket. Not broadcast yet —
 * Phase 6 wires this to Reverb for public live scoring.
 */
class WicketFallen
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Delivery $delivery,
    ) {}
}
