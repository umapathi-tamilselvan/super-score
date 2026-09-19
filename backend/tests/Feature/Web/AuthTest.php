<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_view_the_registration_page(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee('Create Account');
    }

    public function test_a_guest_can_register_and_is_redirected_to_verify_otp(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Arun Kumar',
            'email' => 'arun@example.com',
            'mobile_number' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect(route('otp.verify'));
        $this->assertAuthenticated();

        $user = User::where('email', 'arun@example.com')->first();
        Notification::assertSentTo($user, OtpCodeNotification::class);
    }

    public function test_a_user_can_log_in_and_is_routed_based_on_account_state(): void
    {
        $unverified = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $unverified->email, 'password' => 'password'])
            ->assertRedirect(route('otp.verify'));

        $this->post('/logout');

        $profileIncomplete = User::factory()->profileIncomplete()->create();
        $this->post('/login', ['email' => $profileIncomplete->email, 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->post('/logout');

        $readyUser = User::factory()->create();
        $this->post('/login', ['email' => $readyUser->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guests_cannot_access_authenticated_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_cannot_access_guest_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }
}
