<?php

namespace Tests\Feature\Api;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_a_user_can_create_a_team(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson('/api/v1/teams', [
                'name' => 'Chennai Warriors',
                'short_name' => 'CHW',
                'city' => 'Chennai',
            ]);

        $response->assertStatus(201)->assertJsonPath('data.team.name', 'Chennai Warriors');
        $this->assertDatabaseHas('teams', ['name' => 'Chennai Warriors', 'user_id' => $user->id]);
    }

    public function test_team_creation_requires_a_short_name(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson('/api/v1/teams', ['name' => 'Chennai Warriors']);

        $response->assertStatus(422)->assertJsonValidationErrors('short_name');
    }

    public function test_a_user_only_sees_their_own_teams(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Team::factory()->for($user)->create();
        Team::factory()->for($otherUser)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/teams');

        $response->assertStatus(200)->assertJsonCount(1, 'data.teams');
    }

    public function test_a_user_cannot_view_another_users_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = Team::factory()->for($owner)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($otherUser))
            ->getJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(403);
    }

    public function test_the_owner_can_update_their_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/teams/{$team->id}", ['name' => 'Updated Name']);

        $response->assertStatus(200)->assertJsonPath('data.team.name', 'Updated Name');
    }

    public function test_a_user_cannot_update_another_users_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = Team::factory()->for($owner)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($otherUser))
            ->putJson("/api/v1/teams/{$team->id}", ['name' => 'Hijacked']);

        $response->assertStatus(403);
        $this->assertSame($team->name, $team->fresh()->name);
    }
}
