<?php

namespace Tests\Feature\Api;

use App\Enums\ExtraType;
use App\Models\Innings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SetsUpScoringMatch;
use Tests\TestCase;

class DeliveryProcessingTest extends TestCase
{
    use RefreshDatabase, SetsUpScoringMatch;

    protected Innings $innings;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScoringMatch(overs: 2);
        $this->token = $this->tokenForOrganizer();

        $inningsId = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ])->json('data.innings.id');

        $this->innings = Innings::find($inningsId);
    }

    private function recordBall(array $payload): TestResponse
    {
        return $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/deliveries", $payload);
    }

    public function test_a_normal_delivery_updates_the_score(): void
    {
        $response = $this->recordBall(['runs_off_bat' => 4]);

        $response->assertStatus(201)
            ->assertJsonPath('data.innings.score.runs', 4)
            ->assertJsonPath('data.innings.score.overs', '0.1');
    }

    public function test_odd_runs_rotate_the_strike(): void
    {
        $this->recordBall(['runs_off_bat' => 1]);

        $this->assertSame($this->playersA[1]->id, $this->innings->fresh()->current_striker_id);
    }

    public function test_even_runs_do_not_rotate_the_strike(): void
    {
        $this->recordBall(['runs_off_bat' => 2]);

        $this->assertSame($this->playersA[0]->id, $this->innings->fresh()->current_striker_id);
    }

    public function test_a_wide_is_not_a_legal_delivery_and_is_charged_to_the_bowler(): void
    {
        $response = $this->recordBall(['extra_type' => ExtraType::Wide->value, 'extra_runs' => 1]);

        $response->assertStatus(201)
            ->assertJsonPath('data.innings.score.runs', 1)
            ->assertJsonPath('data.innings.score.overs', '0.0');

        $bowlerStats = collect($response->json('data.innings.bowler_stats'))
            ->firstWhere('player.id', $this->playersB[0]->id);
        $this->assertSame(1, $bowlerStats['runs_conceded']);
    }

    public function test_a_no_ball_credits_runs_off_the_bat_to_the_batter_and_sets_up_a_free_hit(): void
    {
        $response = $this->recordBall(['runs_off_bat' => 4, 'extra_type' => ExtraType::NoBall->value]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.runs', 5);

        $batterStats = collect($response->json('data.innings.batter_stats'))
            ->firstWhere('player.id', $this->playersA[0]->id);
        $this->assertSame(4, $batterStats['runs']);

        // The no-ball itself isn't a free hit; the *next* ball is.
        $this->assertDatabaseHas('deliveries', ['extra_type' => 'no_ball', 'is_free_hit' => false]);

        $this->recordBall(['runs_off_bat' => 2]);
        $this->assertDatabaseHas('deliveries', ['extra_type' => null, 'runs_off_bat' => 2, 'is_free_hit' => true]);
    }

    public function test_a_bye_is_not_charged_to_the_bowler(): void
    {
        $response = $this->recordBall(['extra_type' => ExtraType::Bye->value, 'extra_runs' => 2]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.runs', 2);

        $bowlerStats = collect($response->json('data.innings.bowler_stats'))
            ->firstWhere('player.id', $this->playersB[0]->id);
        $this->assertSame(0, $bowlerStats['runs_conceded']);
    }

    public function test_the_over_completes_after_six_legal_deliveries(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }
        $response = $this->recordBall(['runs_off_bat' => 0]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.overs', '1.0');
    }

    public function test_a_delivery_is_rejected_once_the_over_is_complete_until_the_next_over_starts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }

        $response = $this->recordBall(['runs_off_bat' => 1]);

        $response->assertStatus(422);
    }

    public function test_the_same_bowler_cannot_bowl_two_overs_in_a_row(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/next-over", [
                'bowler_id' => $this->playersB[0]->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('bowler_id');
    }

    public function test_a_different_bowler_can_start_the_next_over(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/next-over", [
                'bowler_id' => $this->playersB[1]->id,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('overs', ['innings_id' => $this->innings->id, 'over_number' => 2, 'bowler_id' => $this->playersB[1]->id]);
    }

    public function test_the_innings_completes_once_the_overs_limit_is_reached(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }

        $this->assertSame('in_progress', $this->innings->fresh()->status->value, 'One of two overs bowled — innings should still be in progress.');

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/next-over", ['bowler_id' => $this->playersB[1]->id]);

        for ($i = 0; $i < 6; $i++) {
            $this->recordBall(['runs_off_bat' => 0]);
        }

        $this->assertSame('completed', $this->innings->fresh()->status->value);
    }

    public function test_a_match_that_isnt_the_organizers_cannot_be_scored_by_someone_else(): void
    {
        $otherUser = User::factory()->create();

        // setUp() already authenticated as the organizer via a real HTTP
        // call to start the innings; Sanctum::actingAs() explicitly
        // overrides the resolved guard user for this request, rather than
        // relying on a Bearer token header, which the guard can otherwise
        // keep resolving to whoever authenticated first in this test.
        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/v1/innings/{$this->innings->id}/deliveries", ['runs_off_bat' => 1]);

        $response->assertStatus(403);
    }
}
