<?php

namespace Tests\Feature\Web;

use App\Enums\PlayerRole;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_players_index_page_renders(): void
    {
        $user = User::factory()->create(['name' => 'Arun Kumar']);
        Player::factory()->for($user)->create();

        $this->actingAs(User::factory()->create())->get(route('players.index'))
            ->assertStatus(200)
            ->assertSee('Arun Kumar');
    }

    public function test_the_player_profile_page_renders_for_a_user_without_a_profile_yet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('player-profile.edit'))
            ->assertStatus(200)
            ->assertSee('Set Up My Player Profile');
    }

    public function test_a_user_can_set_up_their_own_player_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/player-profile', [
            'role' => PlayerRole::Batter->value,
        ]);

        $response->assertRedirect(route('players.index'));
        $this->assertDatabaseHas('players', ['user_id' => $user->id, 'role' => PlayerRole::Batter->value]);
    }

    public function test_a_user_can_update_their_own_player_profile(): void
    {
        $user = User::factory()->create();
        Player::factory()->for($user)->create(['role' => PlayerRole::Batter]);

        $response = $this->actingAs($user)->put('/player-profile', [
            'role' => PlayerRole::Bowler->value,
        ]);

        $response->assertRedirect(route('players.index'));
        $this->assertDatabaseCount('players', 1);
        $this->assertSame(PlayerRole::Bowler, $user->player()->first()->role);
    }
}
