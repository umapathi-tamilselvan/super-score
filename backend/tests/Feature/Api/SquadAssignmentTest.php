<?php

namespace Tests\Feature\Api;

use App\Enums\TeamRole;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SquadAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_any_registered_player_can_be_added_to_a_teams_squad(): void
    {
        $owner = User::factory()->create();
        $playerUser = User::factory()->create();
        $team = Team::factory()->for($owner)->create();
        $player = Player::factory()->for($playerUser)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($owner))
            ->postJson("/api/v1/teams/{$team->id}/players", ['player_id' => $player->id]);

        $response->assertStatus(200)->assertJsonCount(1, 'data.team.players');
        $this->assertDatabaseHas('team_player', ['team_id' => $team->id, 'player_id' => $player->id]);
    }

    public function test_a_player_cannot_be_added_twice_to_the_same_squad(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($user)->create();
        $team->players()->attach($player);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson("/api/v1/teams/{$team->id}/players", ['player_id' => $player->id]);

        $response->assertStatus(422)->assertJsonValidationErrors('player_id');
    }

    public function test_only_the_team_owner_can_add_players_to_the_squad(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $playerUser = User::factory()->create();
        $team = Team::factory()->for($owner)->create();
        $player = Player::factory()->for($playerUser)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($otherUser))
            ->postJson("/api/v1/teams/{$team->id}/players", ['player_id' => $player->id]);

        $response->assertStatus(403);
    }

    public function test_a_player_can_be_removed_from_the_squad(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($user)->create();
        $team->players()->attach($player);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->deleteJson("/api/v1/teams/{$team->id}/players/{$player->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('team_player', ['team_id' => $team->id, 'player_id' => $player->id]);
    }

    public function test_assigning_captain_clears_the_previous_captain(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $firstCaptain = Player::factory()->for(User::factory())->create();
        $secondPlayer = Player::factory()->for(User::factory())->create();
        $team->players()->attach([$firstCaptain->id, $secondPlayer->id]);

        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/teams/{$team->id}/role", [
                'player_id' => $firstCaptain->id,
                'role' => TeamRole::Captain->value,
            ])->assertStatus(200);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/teams/{$team->id}/role", [
                'player_id' => $secondPlayer->id,
                'role' => TeamRole::Captain->value,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_player', [
            'team_id' => $team->id, 'player_id' => $firstCaptain->id, 'is_captain' => false,
        ]);
        $this->assertDatabaseHas('team_player', [
            'team_id' => $team->id, 'player_id' => $secondPlayer->id, 'is_captain' => true,
        ]);
    }

    public function test_a_role_cannot_be_assigned_to_a_player_outside_the_squad(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $outsidePlayer = Player::factory()->for(User::factory())->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/teams/{$team->id}/role", [
                'player_id' => $outsidePlayer->id,
                'role' => TeamRole::Captain->value,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('player_id');
    }
}
