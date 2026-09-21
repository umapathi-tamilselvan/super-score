<?php

namespace Tests\Feature\Api;

use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayingXiValidationTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function squadOf(Team $team, int $count): Collection
    {
        $players = Player::factory()->count($count)->create();
        $team->players()->attach($players->pluck('id'));

        return $players;
    }

    public function test_playing_xi_requires_the_exact_squad_size(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $players = $this->squadOf($teamA, config('cricket.playing_xi_size'));
        $this->squadOf($teamB, config('cricket.playing_xi_size'));
        $match = CricketMatch::factory()->for($user)->create();
        $match->teams()->sync([$teamA->id => ['side' => 'team_a'], $teamB->id => ['side' => 'team_b']]);

        $tooFew = $players->take(config('cricket.playing_xi_size') - 1)->pluck('id')->all();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/playing-xi/{$teamA->id}", [
                'player_ids' => $tooFew,
                'captain_player_id' => $tooFew[0],
                'wicket_keeper_player_id' => $tooFew[1],
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('player_ids');
    }

    public function test_captain_must_be_among_the_selected_players(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $players = $this->squadOf($teamA, config('cricket.playing_xi_size'));
        $this->squadOf($teamB, config('cricket.playing_xi_size'));
        $match = CricketMatch::factory()->for($user)->create();
        $match->teams()->sync([$teamA->id => ['side' => 'team_a'], $teamB->id => ['side' => 'team_b']]);

        $playerIds = $players->pluck('id')->all();
        $outsider = Player::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/playing-xi/{$teamA->id}", [
                'player_ids' => $playerIds,
                'captain_player_id' => $outsider->id,
                'wicket_keeper_player_id' => $playerIds[1],
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('captain_player_id');
    }

    public function test_playing_xi_cannot_be_selected_before_both_teams_are_chosen(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $players = $this->squadOf($teamA, config('cricket.playing_xi_size'));
        $match = CricketMatch::factory()->for($user)->create();

        $playerIds = $players->pluck('id')->all();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/playing-xi/{$teamA->id}", [
                'player_ids' => $playerIds,
                'captain_player_id' => $playerIds[0],
                'wicket_keeper_player_id' => $playerIds[1],
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('team');
    }

    public function test_a_valid_playing_xi_is_saved_with_roles(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $players = $this->squadOf($teamA, config('cricket.playing_xi_size'));
        $this->squadOf($teamB, config('cricket.playing_xi_size'));
        $match = CricketMatch::factory()->for($user)->create();
        $match->teams()->sync([$teamA->id => ['side' => 'team_a'], $teamB->id => ['side' => 'team_b']]);

        $playerIds = $players->pluck('id')->all();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/playing-xi/{$teamA->id}", [
                'player_ids' => $playerIds,
                'captain_player_id' => $playerIds[0],
                'wicket_keeper_player_id' => $playerIds[1],
                'vice_captain_player_id' => $playerIds[2],
            ]);

        $response->assertStatus(200)->assertJsonCount(config('cricket.playing_xi_size'), 'data.playing_xi');
        $this->assertDatabaseHas('playing_xi', [
            'match_id' => $match->id, 'team_id' => $teamA->id, 'player_id' => $playerIds[0], 'is_captain' => true,
        ]);
        $this->assertDatabaseHas('playing_xi', [
            'match_id' => $match->id, 'team_id' => $teamA->id, 'player_id' => $playerIds[1], 'is_wicket_keeper' => true,
        ]);
    }
}
