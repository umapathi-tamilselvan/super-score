<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Arun Kumar',
            'email' => 'arun@example.com',
            'mobile_number' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted' => true,
        ]);

        $response->assertStatus(201)->assertJsonPath('success', true);
        $this->assertDatabaseHas('users', ['email' => 'arun@example.com']);

        $user = User::where('email', 'arun@example.com')->first();
        $this->assertFalse($user->hasVerifiedOtp());

        Notification::assertSentTo($user, OtpCodeNotification::class);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Arun Kumar',
            'email' => 'arun@example.com',
            'mobile_number' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'terms_accepted' => true,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_a_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_an_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
