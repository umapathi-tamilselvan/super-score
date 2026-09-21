<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMatchRequest;
use App\Http\Requests\SelectTeamsRequest;
use App\Http\Resources\InningsResource;
use App\Http\Resources\MatchResource;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Services\Contracts\MatchSetupServiceInterface;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MatchController extends Controller
{
    public function __construct(
        private readonly MatchSetupServiceInterface $matchSetupService,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $matches = $request->user()->organizedMatches()->with('teams')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Matches retrieved successfully.',
            'data' => ['matches' => MatchResource::collection($matches)],
        ]);
    }

    public function store(CreateMatchRequest $request): JsonResponse
    {
        $match = $this->matchSetupService->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Match created successfully.',
            'data' => ['match' => new MatchResource($match)],
        ], 201);
    }

    public function show(CricketMatch $match): JsonResponse
    {
        Gate::authorize('view', $match);

        return response()->json([
            'success' => true,
            'message' => 'Match retrieved successfully.',
            'data' => ['match' => new MatchResource($match->load(['teams', 'toss']))],
        ]);
    }

    public function selectTeams(SelectTeamsRequest $request, CricketMatch $match): JsonResponse
    {
        $teamA = Team::findOrFail($request->validated('team_a_id'));
        $teamB = Team::findOrFail($request->validated('team_b_id'));

        try {
            $this->matchSetupService->selectTeams($match, $teamA, $teamB);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['team_b_id' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teams selected successfully.',
            'data' => ['match' => new MatchResource($match->load('teams'))],
        ]);
    }

    /**
     * Current live state — both innings (if started) with their score
     * projections, for resuming a scoring session or a public viewer.
     */
    public function live(CricketMatch $match): JsonResponse
    {
        Gate::authorize('view', $match);

        $match->load(['teams', 'toss', 'innings' => function ($query) {
            $query->with(['team', 'currentStriker.user', 'currentNonStriker.user', 'currentBowler.user', 'match'])
                ->orderBy('innings_number');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Live match state retrieved successfully.',
            'data' => [
                'match' => new MatchResource($match),
                'innings' => $match->innings->map(fn ($innings) => new InningsResource([
                    'innings' => $innings,
                    'projection' => $this->scoreProjector->project($innings),
                ])),
            ],
        ]);
    }
}
