<?php

namespace Tests\Feature\Api;

use App\Enums\MatchFormat;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSetupTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function teamWithFullSquad(?User $owner = null): Team
    {
        $team = Team::factory()->for($owner ?? User::factory())->create();

        Player::factory()
            ->count(config('cricket.playing_xi_size'))
            ->create()
            ->each(fn (Player $player) => $team->players()->attach($player));

        return $team->load('players');
    }

    public function test_a_user_can_create_a_match(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->postJson('/api/v1/matches', [
                'name' => 'Chennai Warriors vs Tamil Kings',
                'format' => MatchFormat::T20->value,
                'overs' => 20,
                'date' => now()->addWeek()->toDateString(),
                'venue' => 'Chepauk Ground',
            ]);

        $response->assertStatus(201)->assertJsonPath('data.match.name', 'Chennai Warriors vs Tamil Kings');
        $this->assertDatabaseHas('matches', ['user_id' => $user->id, 'status' => 'scheduled']);
    }

    public function test_selecting_teams_requires_two_different_teams(): void
    {
        $user = User::factory()->create();
        $match = CricketMatch::factory()->for($user)->create();
        $team = $this->teamWithFullSquad();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/teams", [
                'team_a_id' => $team->id,
                'team_b_id' => $team->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('team_a_id');
    }

    public function test_selecting_teams_fails_when_a_team_lacks_enough_players(): void
    {
        $user = User::factory()->create();
        $match = CricketMatch::factory()->for($user)->create();
        $fullTeam = $this->teamWithFullSquad();
        $emptyTeam = Team::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/teams", [
                'team_a_id' => $fullTeam->id,
                'team_b_id' => $emptyTeam->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('team_b_id');
    }

    public function test_a_user_can_select_two_valid_teams(): void
    {
        $user = User::factory()->create();
        $match = CricketMatch::factory()->for($user)->create();
        $teamA = $this->teamWithFullSquad();
        $teamB = $this->teamWithFullSquad();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/teams", [
                'team_a_id' => $teamA->id,
                'team_b_id' => $teamB->id,
            ]);

        $response->assertStatus(200)->assertJsonCount(2, 'data.match.teams');
        $this->assertDatabaseHas('match_teams', ['match_id' => $match->id, 'team_id' => $teamA->id, 'side' => 'team_a']);
        $this->assertDatabaseHas('match_teams', ['match_id' => $match->id, 'team_id' => $teamB->id, 'side' => 'team_b']);
    }

    public function test_a_user_cannot_manage_another_users_match(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $match = CricketMatch::factory()->for($owner)->create();
        $teamA = $this->teamWithFullSquad();
        $teamB = $this->teamWithFullSquad();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($otherUser))
            ->putJson("/api/v1/matches/{$match->id}/teams", [
                'team_a_id' => $teamA->id,
                'team_b_id' => $teamB->id,
            ]);

        $response->assertStatus(403);
    }
}
