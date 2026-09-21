<?php

namespace Tests\Feature\Api;

use App\Enums\DismissalType;
use App\Enums\ExtraType;
use App\Models\Innings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\SetsUpScoringMatch;
use Tests\TestCase;

class DismissalTest extends TestCase
{
    use RefreshDatabase, SetsUpScoringMatch;

    protected Innings $innings;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScoringMatch(overs: 20);
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

    public function test_a_bowled_wicket_is_credited_to_the_bowler_and_vacates_the_strikers_slot(): void
    {
        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Bowled->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.wickets', 1);

        $bowlerStats = collect($response->json('data.innings.bowler_stats'))
            ->firstWhere('player.id', $this->playersB[0]->id);
        $this->assertSame(1, $bowlerStats['wickets']);

        $this->assertNull($this->innings->fresh()->current_striker_id);
        $this->assertSame($this->playersA[1]->id, $this->innings->fresh()->current_non_striker_id);
    }

    public function test_a_run_out_is_not_credited_to_the_bowler(): void
    {
        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::RunOut->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $bowlerStats = collect($response->json('data.innings.bowler_stats'))
            ->firstWhere('player.id', $this->playersB[0]->id);
        $this->assertSame(0, $bowlerStats['wickets']);
    }

    public function test_a_caught_dismissal_requires_a_fielder(): void
    {
        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Caught->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_a_caught_dismissal_with_a_fielder_succeeds(): void
    {
        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Caught->value,
            'dismissed_player_id' => $this->playersA[0]->id,
            'fielder_id' => $this->playersB[2]->id,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.wickets', 1);
    }

    public function test_a_caught_dismissal_is_rejected_on_a_free_hit(): void
    {
        $this->recordBall(['runs_off_bat' => 0, 'extra_type' => ExtraType::NoBall->value]);

        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Caught->value,
            'dismissed_player_id' => $this->playersA[0]->id,
            'fielder_id' => $this->playersB[2]->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_a_run_out_is_allowed_on_a_free_hit(): void
    {
        $this->recordBall(['runs_off_bat' => 0, 'extra_type' => ExtraType::NoBall->value]);

        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::RunOut->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.innings.score.wickets', 1);
    }

    public function test_the_dismissed_player_must_be_the_current_striker_or_non_striker(): void
    {
        $response = $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::RunOut->value,
            'dismissed_player_id' => $this->playersA[5]->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_a_new_batter_can_be_selected_after_a_wicket(): void
    {
        $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Bowled->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/new-batter", [
                'player_id' => $this->playersA[2]->id,
            ]);

        $response->assertStatus(200)->assertJsonPath('data.innings.current_striker.id', $this->playersA[2]->id);
    }

    public function test_a_delivery_is_rejected_while_a_batting_slot_is_vacant(): void
    {
        $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Bowled->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response = $this->recordBall(['runs_off_bat' => 1]);

        $response->assertStatus(422);
    }

    public function test_an_already_dismissed_player_cannot_be_selected_as_the_new_batter(): void
    {
        $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Bowled->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/new-batter", [
                'player_id' => $this->playersA[0]->id,
            ]);

        $response->assertStatus(422);
    }
}
