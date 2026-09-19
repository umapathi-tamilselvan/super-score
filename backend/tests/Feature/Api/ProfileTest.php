<?php

namespace Tests\Feature\Api;

use App\Enums\PreferredRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_verified_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)->assertJsonPath('data.user.email', $user->email);
    }

    public function test_completing_the_profile_for_the_first_time_marks_it_complete(): void
    {
        Storage::fake('public');

        $user = User::factory()->profileIncomplete()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/profile', [
                'preferred_role' => PreferredRole::Player->value,
                'photo' => UploadedFile::fake()->image('avatar.png'),
            ]);

        $response->assertStatus(200)->assertJsonPath('data.user.profile_completed', true);

        $user->refresh();
        $this->assertNotNull($user->profile_completed_at);
        $this->assertSame(PreferredRole::Player, $user->preferred_role);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_updating_the_profile_does_not_reset_completion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $originalCompletedAt = $user->profile_completed_at;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/profile', ['name' => 'Updated Name']);

        $response->assertStatus(200);
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertEquals($originalCompletedAt->timestamp, $user->fresh()->profile_completed_at->timestamp);
    }

    public function test_email_must_be_unique_when_updating_profile(): void
    {
        $otherUser = User::factory()->create();
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/profile', ['email' => $otherUser->email]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }
}
