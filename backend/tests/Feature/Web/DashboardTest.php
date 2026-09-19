<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_users_are_redirected_to_otp_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('otp.verify'));
    }

    public function test_users_with_an_incomplete_profile_are_redirected_to_profile_setup(): void
    {
        $user = User::factory()->profileIncomplete()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('profile.edit'));
    }

    public function test_a_fully_onboarded_user_sees_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Hi, '.$user->name);
    }
}
