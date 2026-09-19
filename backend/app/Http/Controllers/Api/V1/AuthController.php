<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\OtpServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly OtpServiceInterface $otpService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        $this->otpService->generateAndSend($user, OtpPurpose::Registration);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify the OTP sent to your email.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->attemptCredentials(
            $request->string('email')->value(),
            $request->string('password')->value(),
        );

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $verified = $this->otpService->verify(
            $request->user(),
            $request->string('code')->value(),
            OtpPurpose::Registration,
        );

        if (! $verified) {
            return response()->json([
                'success' => false,
                'message' => 'The provided code is invalid or has expired.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account verified successfully.',
            'data' => ['user' => new UserResource($request->user()->fresh())],
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $this->otpService->resend($request->user(), OtpPurpose::Registration);

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been sent.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->string('email')->value());

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => __($status),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        return response()->json([
            'success' => $status === Password::PASSWORD_RESET,
            'message' => __($status),
        ]);
    }
}
