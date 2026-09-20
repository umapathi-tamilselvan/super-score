<?php

namespace Tests\Feature\Api;

use App\Enums\PlayerRole;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_a_user_can_set_up_their_own_player_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/player-profile', [
                'role' => PlayerRole::Batter->value,
                'batting_style' => 'Right-hand bat',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.player.role', PlayerRole::Batter->value)
            ->assertJsonPath('data.player.name', $user->name);
        $this->assertDatabaseHas('players', ['user_id' => $user->id]);
    }

    public function test_saving_the_profile_again_updates_the_same_record_instead_of_creating_another(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/player-profile', ['role' => PlayerRole::Batter->value])
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/player-profile', ['role' => PlayerRole::Bowler->value])
            ->assertStatus(200);

        $this->assertDatabaseCount('players', 1);
        $this->assertSame(PlayerRole::Bowler, $user->player()->first()->role);
    }

    public function test_setting_up_a_profile_requires_a_valid_role(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/player-profile', ['role' => 'captain']);

        $response->assertStatus(422)->assertJsonValidationErrors('role');
    }

    public function test_a_user_without_a_profile_gets_null_from_the_show_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/player-profile');

        $response->assertStatus(200)->assertJsonPath('data.player', null);
    }

    public function test_the_directory_lists_every_registered_player(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Player::factory()->for($userA)->create();
        Player::factory()->for($userB)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($userA))
            ->getJson('/api/v1/players');

        $response->assertStatus(200)->assertJsonCount(2, 'data.players');
    }
}
