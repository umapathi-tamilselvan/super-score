<?php

namespace Tests\Feature\Api;

use App\Enums\TossDecision;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TossTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function matchReadyForToss(User $organizer): CricketMatch
    {
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $match = CricketMatch::factory()->for($organizer)->create();
        $match->teams()->sync([$teamA->id => ['side' => 'team_a'], $teamB->id => ['side' => 'team_b']]);

        foreach ([$teamA, $teamB] as $team) {
            $players = Player::factory()->count(config('cricket.playing_xi_size'))->create();
            $team->players()->attach($players->pluck('id'));

            $match->playingXi()->insert($players->map(fn ($player, $index) => [
                'match_id' => $match->id,
                'team_id' => $team->id,
                'player_id' => $player->id,
                'is_captain' => $index === 0,
                'is_vice_captain' => false,
                'is_wicket_keeper' => $index === 1,
                'is_substitute' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        }

        return $match->fresh(['teams']);
    }

    public function test_the_toss_can_be_recorded_once_both_playing_xis_are_set(): void
    {
        $user = User::factory()->create();
        $match = $this->matchReadyForToss($user);
        $winner = $match->teams->first();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/toss", [
                'winner_team_id' => $winner->id,
                'decision' => TossDecision::Bat->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.match.status', 'toss_completed')
            ->assertJsonPath('data.match.toss.decision', 'bat')
            ->assertJsonPath('data.match.toss.batting_team.id', $winner->id);

        $this->assertDatabaseHas('tosses', ['match_id' => $match->id, 'winner_team_id' => $winner->id, 'decision' => 'bat']);
    }

    public function test_the_toss_cannot_be_recorded_before_playing_xis_are_complete(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $match = CricketMatch::factory()->for($user)->create();
        $match->teams()->sync([$teamA->id => ['side' => 'team_a'], $teamB->id => ['side' => 'team_b']]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/toss", [
                'winner_team_id' => $teamA->id,
                'decision' => TossDecision::Bat->value,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('winner_team_id');
    }

    public function test_the_toss_winner_must_be_one_of_the_matchs_teams(): void
    {
        $user = User::factory()->create();
        $match = $this->matchReadyForToss($user);
        $outsiderTeam = Team::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/toss", [
                'winner_team_id' => $outsiderTeam->id,
                'decision' => TossDecision::Bowl->value,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('winner_team_id');
    }

    public function test_recording_the_toss_deriving_bowling_first_when_winner_chooses_to_bowl(): void
    {
        $user = User::factory()->create();
        $match = $this->matchReadyForToss($user);
        $winner = $match->teams->first();
        $other = $match->teams->last();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson("/api/v1/matches/{$match->id}/toss", [
                'winner_team_id' => $winner->id,
                'decision' => TossDecision::Bowl->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.match.toss.batting_team.id', $other->id)
            ->assertJsonPath('data.match.toss.bowling_team.id', $winner->id);
    }
}
