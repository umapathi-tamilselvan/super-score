<?php

namespace App\Services\Scoring;

use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\ExtraOutcome;
use App\Enums\ExtraType;
use App\Enums\InningsStatus;
use App\Events\BallRecorded;
use App\Events\InningsCompleted;
use App\Events\OverCompleted;
use App\Events\WicketFallen;
use App\Models\Delivery;
use App\Models\Innings;
use App\Services\Scoring\Contracts\DeliveryProcessorInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use App\Services\Scoring\Contracts\StrikeRotationInterface;
use App\Services\Scoring\Dismissals\DismissalHandlerFactory;
use App\Services\Scoring\Extras\ExtraHandlerFactory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryProcessingService implements DeliveryProcessorInterface
{
    public function __construct(
        private readonly ExtraHandlerFactory $extraHandlerFactory,
        private readonly DismissalHandlerFactory $dismissalHandlerFactory,
        private readonly StrikeRotationInterface $strikeRotation,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function record(Innings $innings, DeliveryInput $input): Delivery
    {
        if (! $innings->isInProgress()) {
            throw new InvalidArgumentException('This innings has already ended.');
        }

        if (! $innings->current_striker_id || ! $innings->current_non_striker_id) {
            throw new InvalidArgumentException('Select the new batter before recording another delivery.');
        }

        $over = $innings->currentOver();

        if (! $over || $over->isComplete()) {
            throw new InvalidArgumentException('Select the next bowler before recording another delivery.');
        }

        $outcome = $input->extraType
            ? $this->extraHandlerFactory->forType($input->extraType)->apply($input)
            : new ExtraOutcome(isLegalDelivery: true, teamRuns: $input->runsOffBat, batterRuns: $input->runsOffBat);

        $isFreeHit = $innings->deliveries()->latest('sequence_in_innings')->first()?->extra_type === ExtraType::NoBall;

        if ($input->isWicket) {
            $this->validateWicket($input, $innings, $isFreeHit);
        }

        $delivery = DB::transaction(function () use ($innings, $over, $input, $outcome, $isFreeHit) {
            $nextSequence = ($innings->deliveries()->max('sequence_in_innings') ?? 0) + 1;

            $delivery = $innings->deliveries()->create([
                'over_id' => $over->id,
                'striker_id' => $innings->current_striker_id,
                'non_striker_id' => $innings->current_non_striker_id,
                'bowler_id' => $over->bowler_id,
                'runs_off_bat' => $outcome->batterRuns,
                'extra_type' => $input->extraType,
                'extra_runs' => $outcome->teamRuns - $outcome->batterRuns,
                'is_wicket' => $input->isWicket,
                'dismissal_type' => $input->dismissalType,
                'dismissed_player_id' => $input->isWicket ? $input->dismissedPlayerId : null,
                'fielder_id' => $input->isWicket ? $input->fielderId : null,
                'is_legal_delivery' => $outcome->isLegalDelivery,
                'is_free_hit' => $isFreeHit,
                'sequence_in_innings' => $nextSequence,
            ]);

            if ($input->isWicket) {
                $this->vacateDismissedSlot($innings, $input->dismissedPlayerId);
            } else {
                $this->strikeRotation->applyAfterDelivery($innings, $delivery);
            }

            return $delivery;
        });

        BallRecorded::dispatch($delivery);

        if ($delivery->is_wicket) {
            WicketFallen::dispatch($delivery);
        }

        if ($over->fresh()->isComplete()) {
            OverCompleted::dispatch($over);
        }

        $this->checkInningsEndConditions($innings->fresh());

        return $delivery;
    }

    private function validateWicket(DeliveryInput $input, Innings $innings, bool $isFreeHit): void
    {
        if (! $input->dismissalType) {
            throw new InvalidArgumentException('A dismissal type is required for a wicket.');
        }

        if (! in_array($input->dismissedPlayerId, [$innings->current_striker_id, $innings->current_non_striker_id], true)) {
            throw new InvalidArgumentException('The dismissed player must be the current striker or non-striker.');
        }

        $outcome = $this->dismissalHandlerFactory->forType($input->dismissalType)->apply($input);

        if ($isFreeHit && ! $outcome->allowedOnFreeHit) {
            throw new InvalidArgumentException('This dismissal is not allowed on a free hit.');
        }

        if ($outcome->requiresFielder && ! $input->fielderId) {
            throw new InvalidArgumentException('A fielder is required for this dismissal.');
        }
    }

    private function vacateDismissedSlot(Innings $innings, int $dismissedPlayerId): void
    {
        if ($innings->current_striker_id === $dismissedPlayerId) {
            $innings->update(['current_striker_id' => null]);
        } else {
            $innings->update(['current_non_striker_id' => null]);
        }
    }

    private function checkInningsEndConditions(Innings $innings): void
    {
        $projection = $this->scoreProjector->project($innings);
        $allOut = $projection->wickets >= config('cricket.playing_xi_size') - 1;
        $oversComplete = $projection->legalBallsBowled >= $innings->match->overs * 6;

        if ($allOut || $oversComplete) {
            $innings->update(['status' => InningsStatus::Completed]);
            InningsCompleted::dispatch($innings);
        }
    }
}
