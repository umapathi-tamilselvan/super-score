<?php

namespace App\Services\Scoring;

use App\DataTransferObjects\DeliveryInput;
use App\Models\Delivery;
use App\Models\Innings;
use App\Services\Scoring\Contracts\DeliveryProcessorInterface;
use InvalidArgumentException;

/**
 * Ball correction. Because every derived number comes from
 * ScoreProjectionService replaying the delivery list fresh, "editing"
 * a ball is just: undo it, then re-record the corrected input through
 * the same validated path as any other delivery — no separate
 * recalculation logic to keep in sync.
 *
 * Scoped to the most recent delivery in the innings only. Editing an
 * earlier ball could change strike rotation for every delivery bowled
 * after it, which this app doesn't attempt to replay — a known,
 * deliberate limitation for this phase.
 */
class BallRecalculationService
{
    public function __construct(
        private readonly DeliveryProcessorInterface $deliveryProcessor,
    ) {}

    public function undoLastDelivery(Innings $innings): void
    {
        $delivery = $this->requireLastDelivery($innings);

        $innings->update([
            'current_striker_id' => $delivery->striker_id,
            'current_non_striker_id' => $delivery->non_striker_id,
            'current_bowler_id' => $delivery->bowler_id,
        ]);

        $delivery->delete();
    }

    public function editLastDelivery(Innings $innings, DeliveryInput $newInput): Delivery
    {
        $this->requireLastDelivery($innings);

        $this->undoLastDelivery($innings);

        return $this->deliveryProcessor->record($innings->fresh(), $newInput);
    }

    private function requireLastDelivery(Innings $innings): Delivery
    {
        $delivery = $innings->deliveries()->latest('sequence_in_innings')->first();

        if (! $delivery) {
            throw new InvalidArgumentException('There is no delivery to correct yet.');
        }

        return $delivery;
    }
}
