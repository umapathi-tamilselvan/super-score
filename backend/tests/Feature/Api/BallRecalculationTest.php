<?php

namespace Tests\Feature\Api;

use App\Enums\DismissalType;
use App\Models\Innings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\SetsUpScoringMatch;
use Tests\TestCase;

class BallRecalculationTest extends TestCase
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

    public function test_the_last_delivery_can_be_undone(): void
    {
        $this->recordBall(['runs_off_bat' => 4]);
        $this->recordBall(['runs_off_bat' => 1]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/deliveries/undo");

        $response->assertStatus(200)->assertJsonPath('data.innings.score.runs', 4);
        $this->assertDatabaseCount('deliveries', 1);
        // The undone ball (1 run, odd) had rotated strike to playersA[1];
        // undoing it restores who was on strike *before* that ball.
        $this->assertSame($this->playersA[0]->id, $this->innings->fresh()->current_striker_id);
    }

    public function test_undoing_a_wicket_restores_both_batting_slots(): void
    {
        $this->recordBall([
            'runs_off_bat' => 0,
            'is_wicket' => true,
            'dismissal_type' => DismissalType::Bowled->value,
            'dismissed_player_id' => $this->playersA[0]->id,
        ]);
        $this->assertNull($this->innings->fresh()->current_striker_id);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/deliveries/undo")
            ->assertStatus(200);

        $this->assertSame($this->playersA[0]->id, $this->innings->fresh()->current_striker_id);
        $this->assertDatabaseCount('deliveries', 0);
    }

    public function test_the_last_delivery_can_be_edited(): void
    {
        $this->recordBall(['runs_off_bat' => 1]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/innings/{$this->innings->id}/deliveries/last", ['runs_off_bat' => 6]);

        $response->assertStatus(200)->assertJsonPath('data.innings.score.runs', 6);
        $this->assertDatabaseCount('deliveries', 1);
        $this->assertDatabaseHas('deliveries', ['runs_off_bat' => 6]);
    }

    public function test_undo_fails_when_there_is_no_delivery_yet(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/innings/{$this->innings->id}/deliveries/undo");

        $response->assertStatus(422);
    }
}
