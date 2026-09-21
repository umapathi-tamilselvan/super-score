<?php

namespace App\Http\Controllers\Web;

use App\Enums\MatchStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasCompletedProfile()) {
            return redirect()->route('profile.edit');
        }

        $matches = $user->organizedMatches()->with('teams')->get();

        return view('pages.dashboard', [
            'user' => $user,
            'liveMatches' => $matches->where('status', MatchStatus::InProgress),
            'upcomingMatches' => $matches->whereIn('status', [MatchStatus::Scheduled, MatchStatus::TossCompleted]),
            'recentMatches' => $matches->whereIn('status', [MatchStatus::Completed, MatchStatus::Abandoned]),
        ]);
    }
}
