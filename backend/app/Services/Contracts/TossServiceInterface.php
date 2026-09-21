<?php

namespace App\Services\Contracts;

use App\Enums\TossDecision;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Toss;

interface TossServiceInterface
{
    /**
     * Record (or re-record) the toss result for a match and mark it
     * ready to start. Idempotent — recording again replaces the
     * previous result.
     */
    public function record(CricketMatch $match, Team $winner, TossDecision $decision): Toss;
}
