<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePlayerProfileRequest;
use App\Models\Player;
use App\Services\Contracts\PlayerServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function __construct(
        private readonly PlayerServiceInterface $playerService,
    ) {}

    /**
     * The directory of all registered players — any team owner can
     * browse this to add players to their squad.
     */
    public function index(): View
    {
        return view('pages.players.index', [
            'players' => Player::with('user')->latest()->get(),
        ]);
    }

    /**
     * Create-or-edit form for the current user's own player profile.
     */
    public function editOwn(): View
    {
        return view('pages.players.profile', ['player' => auth()->user()->player]);
    }

    public function updateOwn(SavePlayerProfileRequest $request): RedirectResponse
    {
        $this->playerService->saveOwnProfile($request->user(), $request->validated());

        return redirect()->route('players.index')->with('status', 'Your player profile has been saved.');
    }
}
