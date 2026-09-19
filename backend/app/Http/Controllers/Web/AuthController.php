<?php

namespace App\Http\Controllers\Web;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\OtpServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly OtpServiceInterface $otpService,
    ) {}

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request->validated());

        $this->otpService->generateAndSend($user, OtpPurpose::Registration);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('otp.verify');
    }

    public function showLogin(): View
    {
        return view('pages.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authService->attemptCredentials(
            $request->string('email')->value(),
            $request->string('password')->value(),
        );

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (! $user->hasVerifiedOtp()) {
            return redirect()->route('otp.verify');
        }

        if (! $user->hasCompletedProfile()) {
            return redirect()->route('profile.edit');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showVerifyOtp(): View
    {
        return view('pages.auth.verify-otp');
    }

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $verified = $this->otpService->verify(
            $request->user(),
            $request->string('code')->value(),
            OtpPurpose::Registration,
        );

        if (! $verified) {
            return back()->withErrors(['code' => 'The provided code is invalid or has expired.']);
        }

        return redirect()->route('profile.edit');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $this->otpService->resend($request->user(), OtpPurpose::Registration);

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function showForgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function forgotPassword(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->string('email')->value());

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token, Request $request): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
