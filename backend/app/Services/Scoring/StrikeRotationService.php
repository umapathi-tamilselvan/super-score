<?php

namespace App\Services\Scoring;

use App\Enums\ExtraType;
use App\Models\Delivery;
use App\Models\Innings;
use App\Services\Scoring\Contracts\StrikeRotationInterface;

class StrikeRotationService implements StrikeRotationInterface
{
    public function applyAfterDelivery(Innings $innings, Delivery $delivery): void
    {
        if ($delivery->is_wicket || ! $this->runsRotateStrike($delivery)) {
            return;
        }

        $innings->update([
            'current_striker_id' => $innings->current_non_striker_id,
            'current_non_striker_id' => $innings->current_striker_id,
        ]);
    }

    private function runsRotateStrike(Delivery $delivery): bool
    {
        return match ($delivery->extra_type) {
            null, ExtraType::NoBall => $delivery->runs_off_bat % 2 === 1,
            ExtraType::Bye, ExtraType::LegBye => $delivery->extra_runs % 2 === 1,
            ExtraType::Wide => ($delivery->extra_runs - 1) % 2 === 1,
            ExtraType::Penalty => false,
        };
    }
}
