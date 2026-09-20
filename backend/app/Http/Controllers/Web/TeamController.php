<?php

namespace App\Http\Controllers\Web;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignPlayerRequest;
use App\Http\Requests\AssignTeamRoleRequest;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Player;
use App\Models\Team;
use App\Services\Contracts\PlayerServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamServiceInterface $teamService,
        private readonly PlayerServiceInterface $playerService,
    ) {}

    public function index(Request $request): View
    {
        return view('pages.teams.index', [
            'teams' => $request->user()->teams()->withCount('players')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.teams.create');
    }

    public function store(CreateTeamRequest $request): RedirectResponse
    {
        $team = $this->teamService->create($request->user(), $request->validated());

        return redirect()->route('teams.show', $team)->with('status', 'Team created successfully.');
    }

    public function show(Team $team): View
    {
        Gate::authorize('view', $team);

        $team->load('players.user');

        return view('pages.teams.show', [
            'team' => $team,
            'availablePlayers' => Player::with('user')
                ->whereNotIn('id', $team->players->pluck('id'))
                ->get(),
            'roles' => TeamRole::cases(),
        ]);
    }

    public function edit(Team $team): View
    {
        Gate::authorize('update', $team);

        return view('pages.teams.edit', ['team' => $team]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $this->teamService->update($team, $request->validated());

        return redirect()->route('teams.show', $team)->with('status', 'Team updated successfully.');
    }

    public function addPlayer(AssignPlayerRequest $request, Team $team): RedirectResponse
    {
        $player = Player::findOrFail($request->validated('player_id'));

        $this->playerService->attachToTeam($player, $team);

        return back()->with('status', 'Player added to the squad.');
    }

    public function removePlayer(Team $team, Player $player): RedirectResponse
    {
        Gate::authorize('update', $team);

        $this->playerService->detachFromTeam($player, $team);

        return back()->with('status', 'Player removed from the squad.');
    }

    public function assignRole(AssignTeamRoleRequest $request, Team $team): RedirectResponse
    {
        $player = Player::findOrFail($request->validated('player_id'));

        $this->teamService->assignRole($team, $player, $request->enum('role', TeamRole::class));

        return back()->with('status', 'Role assigned successfully.');
    }
}
