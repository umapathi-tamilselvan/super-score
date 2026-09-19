<?php

namespace Tests\Feature\Web;

use App\Enums\PreferredRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_first_time_user_completes_their_profile_and_reaches_the_dashboard(): void
    {
        Storage::fake('public');

        $user = User::factory()->profileIncomplete()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'preferred_role' => PreferredRole::Scorer->value,
            'photo' => UploadedFile::fake()->image('avatar.png'),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->hasCompletedProfile());
    }
}
