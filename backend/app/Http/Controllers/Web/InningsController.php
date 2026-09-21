<?php

namespace App\Http\Controllers\Web;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class InningsController extends Controller
{
    public function __construct(
        private readonly InningsServiceInterface $inningsService,
        private readonly OverManagerService $overManager,
        private readonly ScoreProjectorInterface $scoreProjector,
    ) {}

    public function create(CricketMatch $match): View
    {
        Gate::authorize('update', $match);

        $match->load(['teams', 'toss', 'innings']);
        $previousInnings = $match->innings->sortByDesc('innings_number')->first();
        $battingTeam = $previousInnings
            ? $match->teams->firstWhere('id', '!=', $previousInnings->team_id)
            : $match->toss->battingTeam();
        $bowlingTeam = $match->teams->firstWhere('id', '!=', $battingTeam->id);

        return view('pages.matches.start-innings', [
            'match' => $match,
            'battingTeam' => $battingTeam->load('players.user'),
            'bowlingTeam' => $bowlingTeam->load('players.user'),
        ]);
    }

    public function store(StartInningsRequest $request, CricketMatch $match): RedirectResponse
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

        return redirect()->route('innings.live', $innings);
    }

    public function live(Innings $innings): View
    {
        Gate::authorize('view', $innings->match);

        $innings->load('match.teams');
        $battingTeamXi = $innings->team->players()->with('user')->get();
        $bowlingTeam = $innings->match->teams->firstWhere('id', '!=', $innings->team_id);
        $bowlingTeamXi = $bowlingTeam->players()->with('user')->get();

        return view('pages.matches.live', [
            'innings' => $innings,
            'initialState' => $this->present($innings),
            'battingTeamXi' => $battingTeamXi,
            'bowlingTeamXi' => $bowlingTeamXi,
        ]);
    }

    public function show(Innings $innings): JsonResponse
    {
        Gate::authorize('view', $innings->match);

        return response()->json(['success' => true, 'data' => ['innings' => $this->present($innings)]]);
    }

    public function selectNewBatter(SelectNewBatterRequest $request, Innings $innings): JsonResponse
    {
        $newBatter = Player::findOrFail($request->validated('player_id'));

        try {
            $this->inningsService->selectNewBatter($innings, $newBatter);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['player_id' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'data' => ['innings' => $this->present($innings->fresh())]]);
    }

    public function startNextOver(StartNextOverRequest $request, Innings $innings): JsonResponse
    {
        $bowler = Player::findOrFail($request->validated('bowler_id'));

        try {
            $this->overManager->startNextOver($innings, $bowler);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['bowler_id' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'data' => ['innings' => $this->present($innings->fresh())]]);
    }

    private function present(Innings $innings): InningsResource
    {
        $innings->load(['team', 'currentStriker.user', 'currentNonStriker.user', 'currentBowler.user', 'match']);

        return new InningsResource(['innings' => $innings, 'projection' => $this->scoreProjector->project($innings)]);
    }
}
