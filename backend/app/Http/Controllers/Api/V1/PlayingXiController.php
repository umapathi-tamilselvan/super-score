<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectPlayingXiRequest;
use App\Http\Resources\PlayingXiResource;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Services\Contracts\PlayingXiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PlayingXiController extends Controller
{
    public function __construct(
        private readonly PlayingXiServiceInterface $playingXiService,
    ) {}

    public function update(SelectPlayingXiRequest $request, CricketMatch $match, Team $team): JsonResponse
    {
        try {
            $this->playingXiService->selectXi(
                $match,
                $team,
                $request->validated('player_ids'),
                $request->validated('captain_player_id'),
                $request->validated('wicket_keeper_player_id'),
                $request->validated('vice_captain_player_id'),
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['player_ids' => $e->getMessage()]);
        }

        $xi = $match->playingXi()->where('team_id', $team->id)->with('player.user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Playing XI saved successfully.',
            'data' => ['playing_xi' => PlayingXiResource::collection($xi)],
        ]);
    }
}
