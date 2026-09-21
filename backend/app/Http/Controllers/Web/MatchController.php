<?php

namespace App\Http\Controllers\Web;

use App\Enums\TossDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMatchRequest;
use App\Http\Requests\RecordTossRequest;
use App\Http\Requests\SelectPlayingXiRequest;
use App\Http\Requests\SelectTeamsRequest;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Services\Contracts\MatchSetupServiceInterface;
use App\Services\Contracts\PlayingXiServiceInterface;
use App\Services\Contracts\TossServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class MatchController extends Controller
{
    public function __construct(
        private readonly MatchSetupServiceInterface $matchSetupService,
        private readonly PlayingXiServiceInterface $playingXiService,
        private readonly TossServiceInterface $tossService,
    ) {}

    public function index(Request $request): View
    {
        return view('pages.matches.index', [
            'matches' => $request->user()->organizedMatches()->with('teams')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.matches.create');
    }

    public function store(CreateMatchRequest $request): RedirectResponse
    {
        $match = $this->matchSetupService->create($request->user(), $request->validated());

        return redirect()->route('matches.teams.edit', $match)->with('status', 'Match created. Now select the two teams.');
    }

    public function show(CricketMatch $match): View
    {
        Gate::authorize('view', $match);

        return view('pages.matches.show', ['match' => $match->load(['teams', 'toss', 'playingXi.player.user', 'innings'])]);
    }

    public function editTeams(CricketMatch $match): View
    {
        Gate::authorize('update', $match);

        return view('pages.matches.select-teams', [
            'match' => $match->load('teams'),
            'teams' => Team::with('user')->get(),
        ]);
    }

    public function updateTeams(SelectTeamsRequest $request, CricketMatch $match): RedirectResponse
    {
        $teamA = Team::findOrFail($request->validated('team_a_id'));
        $teamB = Team::findOrFail($request->validated('team_b_id'));

        try {
            $this->matchSetupService->selectTeams($match, $teamA, $teamB);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['team_b_id' => $e->getMessage()]);
        }

        return redirect()->route('matches.playing-xi.edit', [$match, $teamA])
            ->with('status', 'Teams selected. Now choose the playing XI for '.$teamA->name.'.');
    }

    public function editPlayingXi(CricketMatch $match, Team $team): View
    {
        Gate::authorize('update', $match);

        return view('pages.matches.select-playing-xi', [
            'match' => $match,
            'team' => $team->load('players.user'),
        ]);
    }

    public function updatePlayingXi(SelectPlayingXiRequest $request, CricketMatch $match, Team $team): RedirectResponse
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

        $otherTeam = $match->teams->firstWhere('id', '!=', $team->id);

        if (! $match->hasCompletePlayingXiFor($otherTeam)) {
            return redirect()->route('matches.playing-xi.edit', [$match, $otherTeam])
                ->with('status', 'Playing XI saved. Now choose the playing XI for '.$otherTeam->name.'.');
        }

        return redirect()->route('matches.toss.edit', $match)->with('status', 'Both playing XIs are set. Time for the toss.');
    }

    public function editToss(CricketMatch $match): View
    {
        Gate::authorize('update', $match);

        return view('pages.matches.toss', ['match' => $match->load('teams')]);
    }

    public function updateToss(RecordTossRequest $request, CricketMatch $match): RedirectResponse
    {
        $winner = Team::findOrFail($request->validated('winner_team_id'));

        $this->tossService->record($match, $winner, $request->enum('decision', TossDecision::class));

        return redirect()->route('matches.show', $match)->with('status', 'Toss recorded. The match is ready to start.');
    }
}
