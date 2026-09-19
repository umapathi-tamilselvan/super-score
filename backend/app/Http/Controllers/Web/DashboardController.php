<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasCompletedProfile()) {
            return redirect()->route('profile.edit');
        }

        return view('pages.dashboard', [
            'user' => $request->user(),
        ]);
    }
}
