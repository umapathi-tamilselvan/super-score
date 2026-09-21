<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TossDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordTossRequest;
use App\Http\Resources\MatchResource;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Services\Contracts\TossServiceInterface;
use Illuminate\Http\JsonResponse;

class TossController extends Controller
{
    public function __construct(
        private readonly TossServiceInterface $tossService,
    ) {}

    public function update(RecordTossRequest $request, CricketMatch $match): JsonResponse
    {
        $winner = Team::findOrFail($request->validated('winner_team_id'));

        $this->tossService->record($match, $winner, $request->enum('decision', TossDecision::class));

        return response()->json([
            'success' => true,
            'message' => 'Toss recorded successfully.',
            'data' => ['match' => new MatchResource($match->load(['teams', 'toss']))],
        ]);
    }
}
