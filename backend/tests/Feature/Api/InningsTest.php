<?php

namespace Tests\Feature\Api;

use App\DataTransferObjects\DeliveryInput;
use App\Enums\InningsStatus;
use App\Models\Innings;
use App\Models\Player;
use App\Models\User;
use App\Services\Scoring\Contracts\DeliveryProcessorInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SetsUpScoringMatch;
use Tests\TestCase;

class InningsTest extends TestCase
{
    use RefreshDatabase, SetsUpScoringMatch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScoringMatch(overs: 5);
    }

    public function test_the_organizer_can_start_the_first_innings(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenForOrganizer())
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.innings.innings_number', 1)
            ->assertJsonPath('data.innings.current_striker.id', $this->playersA[0]->id)
            ->assertJsonPath('data.innings.target', null);

        $this->assertDatabaseHas('overs', ['bowler_id' => $this->playersB[0]->id, 'over_number' => 1]);
    }

    public function test_striker_and_non_striker_must_be_different(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenForOrganizer())
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[0]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('striker_player_id');
    }

    public function test_openers_must_belong_to_the_correct_teams_playing_xi(): void
    {
        $outsider = Player::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenForOrganizer())
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $outsider->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('striker_player_id');
    }

    public function test_a_user_who_is_not_the_organizer_cannot_start_an_innings(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$otherUser->createToken('t')->plainTextToken)
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_the_second_innings_target_is_the_first_innings_score_plus_one(): void
    {
        $token = $this->tokenForOrganizer();

        $firstId = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ])->json('data.innings.id');

        $innings = Innings::find($firstId);
        app(DeliveryProcessorInterface::class)
            ->record($innings, new DeliveryInput(runsOffBat: 6));
        $innings->update(['status' => InningsStatus::Completed]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamB->id,
                'striker_player_id' => $this->playersB[0]->id,
                'non_striker_player_id' => $this->playersB[1]->id,
                'opening_bowler_player_id' => $this->playersA[0]->id,
            ]);

        $response->assertStatus(201)->assertJsonPath('data.innings.innings_number', 2);
        $this->assertSame(7, $response->json('data.innings.target'));
    }

    public function test_the_second_innings_cannot_start_before_the_first_completes(): void
    {
        $token = $this->tokenForOrganizer();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamA->id,
                'striker_player_id' => $this->playersA[0]->id,
                'non_striker_player_id' => $this->playersA[1]->id,
                'opening_bowler_player_id' => $this->playersB[0]->id,
            ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/matches/{$this->scoringMatch->id}/innings", [
                'team_id' => $this->teamB->id,
                'striker_player_id' => $this->playersB[0]->id,
                'non_striker_player_id' => $this->playersB[1]->id,
                'opening_bowler_player_id' => $this->playersA[0]->id,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('team_id');
    }
}
