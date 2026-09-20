<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignPlayerRequest;
use App\Http\Requests\AssignTeamRoleRequest;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Http\Resources\TeamSquadResource;
use App\Models\Player;
use App\Models\Team;
use App\Services\Contracts\PlayerServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamServiceInterface $teamService,
        private readonly PlayerServiceInterface $playerService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->withCount('players')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Teams retrieved successfully.',
            'data' => ['teams' => TeamResource::collection($teams)],
        ]);
    }

    public function store(CreateTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully.',
            'data' => ['team' => new TeamResource($team)],
        ], 201);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('view', $team);

        return response()->json([
            'success' => true,
            'message' => 'Team retrieved successfully.',
            'data' => ['team' => new TeamSquadResource($team->load('players.user'))],
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $team = $this->teamService->update($team, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully.',
            'data' => ['team' => new TeamResource($team)],
        ]);
    }

    public function addPlayer(AssignPlayerRequest $request, Team $team): JsonResponse
    {
        $player = Player::findOrFail($request->validated('player_id'));

        $this->playerService->attachToTeam($player, $team);

        return response()->json([
            'success' => true,
            'message' => 'Player added to the squad.',
            'data' => ['team' => new TeamSquadResource($team->load('players.user'))],
        ]);
    }

    public function removePlayer(Request $request, Team $team, Player $player): JsonResponse
    {
        Gate::authorize('update', $team);

        $this->playerService->detachFromTeam($player, $team);

        return response()->json([
            'success' => true,
            'message' => 'Player removed from the squad.',
        ]);
    }

    public function assignRole(AssignTeamRoleRequest $request, Team $team): JsonResponse
    {
        $player = Player::findOrFail($request->validated('player_id'));

        $this->teamService->assignRole($team, $player, $request->enum('role', TeamRole::class));

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
            'data' => ['team' => new TeamSquadResource($team->load('players.user'))],
        ]);
    }
}
