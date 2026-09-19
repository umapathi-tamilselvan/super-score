<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Contracts\ProfileServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileServiceInterface $profileService,
    ) {}

    public function edit(Request $request): View
    {
        return view('pages.profile.edit', [
            'user' => $request->user(),
            'isFirstTimeSetup' => ! $request->user()->hasCompletedProfile(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->update($request->user(), $request->validated());

        return redirect()->route('dashboard')->with('status', 'Profile saved successfully.');
    }
}
