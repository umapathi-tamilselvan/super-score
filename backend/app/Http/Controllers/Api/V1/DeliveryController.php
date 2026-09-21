<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordDeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\InningsResource;
use App\Models\Innings;
use App\Services\Scoring\BallRecalculationService;
use App\Services\Scoring\Contracts\DeliveryProcessorInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly DeliveryProcessorInterface $deliveryProcessor,
        private readonly BallRecalculationService $ballRecalculation,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function store(RecordDeliveryRequest $request, Innings $innings): JsonResponse
    {
        try {
            $this->deliveryProcessor->record($innings, $request->toDeliveryInput());
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['runs_off_bat' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery recorded successfully.',
            'data' => ['innings' => $this->present($innings->fresh())],
        ], 201);
    }

    public function undo(Innings $innings): JsonResponse
    {
        Gate::authorize('update', $innings->match);

        try {
            $this->ballRecalculation->undoLastDelivery($innings);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['delivery' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Last delivery undone.',
            'data' => ['innings' => $this->present($innings->fresh())],
        ]);
    }

    public function updateLast(RecordDeliveryRequest $request, Innings $innings): JsonResponse
    {
        try {
            $this->ballRecalculation->editLastDelivery($innings, $request->toDeliveryInput());
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['runs_off_bat' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Last delivery updated successfully.',
            'data' => ['innings' => $this->present($innings->fresh())],
        ]);
    }

    public function history(Innings $innings): JsonResponse
    {
        Gate::authorize('view', $innings->match);

        $deliveries = $innings->deliveries()
            ->with(['over', 'striker.user', 'bowler.user', 'dismissedPlayer.user'])
            ->orderByDesc('sequence_in_innings')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ball history retrieved successfully.',
            'data' => ['deliveries' => DeliveryResource::collection($deliveries)],
        ]);
    }

    private function present(Innings $innings): InningsResource
    {
        $innings->load(['team', 'currentStriker.user', 'currentNonStriker.user', 'currentBowler.user', 'match']);

        return new InningsResource(['innings' => $innings, 'projection' => $this->scoreProjector->project($innings)]);
    }
}
