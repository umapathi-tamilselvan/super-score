<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePlayerProfileRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Services\Contracts\PlayerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct(
        private readonly PlayerServiceInterface $playerService,
    ) {}

    /**
     * The directory of all registered players — any team owner can
     * browse this to add players to their squad.
     */
    public function index(): JsonResponse
    {
        $players = Player::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Players retrieved successfully.',
            'data' => ['players' => PlayerResource::collection($players)],
        ]);
    }

    public function showOwn(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Player profile retrieved successfully.',
            'data' => ['player' => $request->user()->player ? new PlayerResource($request->user()->player) : null],
        ]);
    }

    public function updateOwn(SavePlayerProfileRequest $request): JsonResponse
    {
        $player = $this->playerService->saveOwnProfile($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Player profile saved successfully.',
            'data' => ['player' => new PlayerResource($player)],
        ]);
    }
}
