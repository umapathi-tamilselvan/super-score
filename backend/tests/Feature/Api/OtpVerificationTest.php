<?php

namespace Tests\Feature\Api;

use App\Enums\OtpPurpose;
use App\Models\User;
use App\Services\Contracts\OtpServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_verify_their_account_with_a_valid_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $otpCode = app(OtpServiceInterface::class)->generateAndSend($user, OtpPurpose::Registration);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/otp/verify', ['code' => $otpCode->code]);

        $response->assertStatus(200)->assertJsonPath('data.user.otp_verified', true);
        $this->assertTrue($user->fresh()->hasVerifiedOtp());
    }

    public function test_verification_fails_with_an_invalid_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        app(OtpServiceInterface::class)->generateAndSend($user, OtpPurpose::Registration);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/otp/verify', ['code' => '000000']);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $this->assertFalse($user->fresh()->hasVerifiedOtp());
    }

    public function test_verification_fails_with_an_expired_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $otpCode = $user->otpCodes()->create([
            'code' => '123456',
            'purpose' => OtpPurpose::Registration,
            'expires_at' => now()->subMinute(),
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/otp/verify', ['code' => $otpCode->code]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_user_can_resend_the_otp_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $first = app(OtpServiceInterface::class)->generateAndSend($user, OtpPurpose::Registration);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/otp/resend');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertTrue($first->fresh()->isConsumed());
        $this->assertDatabaseCount('otp_codes', 2);
    }

    public function test_unverified_users_are_blocked_from_protected_endpoints(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/profile');

        $response->assertStatus(403);
    }
}
