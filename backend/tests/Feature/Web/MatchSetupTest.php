<?php

namespace Tests\Feature\Web;

use App\Enums\MatchFormat;
use App\Enums\TossDecision;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSetupTest extends TestCase
{
    use RefreshDatabase;

    private function squadOf(Team $team, int $count): Collection
    {
        $players = Player::factory()->count($count)->create();
        $team->players()->attach($players->pluck('id'));

        return $players;
    }

    public function test_the_matches_index_page_renders(): void
    {
        $user = User::factory()->create();
        CricketMatch::factory()->for($user)->create(['name' => 'Weekend Friendly']);

        $this->actingAs($user)->get(route('matches.index'))
            ->assertStatus(200)
            ->assertSee('Weekend Friendly');
    }

    public function test_a_user_can_walk_through_the_full_match_setup_flow(): void
    {
        $organizer = User::factory()->create();
        $teamA = Team::factory()->create(['name' => 'Chennai Warriors']);
        $teamB = Team::factory()->create(['name' => 'Tamil Kings']);
        $playersA = $this->squadOf($teamA, config('cricket.playing_xi_size'));
        $playersB = $this->squadOf($teamB, config('cricket.playing_xi_size'));

        $this->actingAs($organizer);

        // 1. Create the match.
        $createResponse = $this->post('/matches', [
            'name' => 'Chennai Warriors vs Tamil Kings',
            'format' => MatchFormat::T20->value,
            'overs' => 20,
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Chepauk Ground',
        ]);
        $match = CricketMatch::first();
        $createResponse->assertRedirect(route('matches.teams.edit', $match));

        // 2. Select teams.
        $this->put(route('matches.teams.update', $match), [
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
        ])->assertRedirect(route('matches.playing-xi.edit', [$match, $teamA]));

        // 3. Select Team A's playing XI. IDs are sent as strings, like a
        // real HTML form submits them (not native PHP ints) — this is
        // what caught a real strict-comparison bug in PlayingXiService.
        $this->put(route('matches.playing-xi.update', [$match, $teamA]), [
            'player_ids' => $playersA->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'captain_player_id' => (string) $playersA[0]->id,
            'wicket_keeper_player_id' => (string) $playersA[1]->id,
        ])->assertRedirect(route('matches.playing-xi.edit', [$match, $teamB]));

        // 4. Select Team B's playing XI.
        $this->put(route('matches.playing-xi.update', [$match, $teamB]), [
            'player_ids' => $playersB->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'captain_player_id' => (string) $playersB[0]->id,
            'wicket_keeper_player_id' => (string) $playersB[1]->id,
        ])->assertRedirect(route('matches.toss.edit', $match));

        // 5. Record the toss.
        $this->put(route('matches.toss.update', $match), [
            'winner_team_id' => $teamA->id,
            'decision' => TossDecision::Bat->value,
        ])->assertRedirect(route('matches.show', $match));

        $this->assertDatabaseHas('tosses', ['match_id' => $match->id, 'winner_team_id' => $teamA->id]);
        $this->assertSame('toss_completed', $match->fresh()->status->value);

        // 6. The overview page reflects the completed setup, ready to start.
        $this->get(route('matches.show', $match))
            ->assertStatus(200)
            ->assertSee('won the toss')
            ->assertSee('Start Match')
            ->assertSee('Chennai Warriors');
    }

    public function test_a_user_cannot_manage_another_users_match(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $match = CricketMatch::factory()->for($owner)->create();

        $this->actingAs($otherUser)->get(route('matches.teams.edit', $match))->assertStatus(403);
    }
}
