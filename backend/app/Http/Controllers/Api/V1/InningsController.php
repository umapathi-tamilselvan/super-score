<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectNewBatterRequest;
use App\Http\Requests\StartInningsRequest;
use App\Http\Requests\StartNextOverRequest;
use App\Http\Resources\InningsResource;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\Player;
use App\Models\Team;
use App\Services\Scoring\Contracts\InningsServiceInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use App\Services\Scoring\OverManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InningsController extends Controller
{
    public function __construct(
        private readonly InningsServiceInterface $inningsService,
        private readonly OverManagerService $overManager,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function store(StartInningsRequest $request, CricketMatch $match): JsonResponse
    {
        $battingTeam = Team::findOrFail($request->validated('team_id'));
        $striker = Player::findOrFail($request->validated('striker_player_id'));
        $nonStriker = Player::findOrFail($request->validated('non_striker_player_id'));
        $bowler = Player::findOrFail($request->validated('opening_bowler_player_id'));

        try {
            $innings = $this->inningsService->start($match, $battingTeam, $striker, $nonStriker, $bowler);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['team_id' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Innings started successfully.',
            'data' => ['innings' => $this->present($innings)],
        ], 201);
    }

    public function show(Innings $innings): JsonResponse
    {
        Gate::authorize('view', $innings->match);

        return response()->json([
            'success' => true,
            'message' => 'Innings retrieved successfully.',
            'data' => ['innings' => $this->present($innings)],
        ]);
    }

    public function selectNewBatter(SelectNewBatterRequest $request, Innings $innings): JsonResponse
    {
        $newBatter = Player::findOrFail($request->validated('player_id'));

        try {
            $this->inningsService->selectNewBatter($innings, $newBatter);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['player_id' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'New batter selected successfully.',
            'data' => ['innings' => $this->present($innings->fresh())],
        ]);
    }

    public function startNextOver(StartNextOverRequest $request, Innings $innings): JsonResponse
    {
        $bowler = Player::findOrFail($request->validated('bowler_id'));

        try {
            $this->overManager->startNextOver($innings, $bowler);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['bowler_id' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Next over started successfully.',
            'data' => ['innings' => $this->present($innings->fresh())],
        ]);
    }

    private function present(Innings $innings): InningsResource
    {
        $innings->load(['team', 'currentStriker.user', 'currentNonStriker.user', 'currentBowler.user', 'match']);

        return new InningsResource(['innings' => $innings, 'projection' => $this->scoreProjector->project($innings)]);
    }
}
