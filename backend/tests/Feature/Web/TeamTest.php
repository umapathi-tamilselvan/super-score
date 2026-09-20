<?php

namespace Tests\Feature\Web;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_teams_index_page_renders(): void
    {
        $user = User::factory()->create();
        Team::factory()->for($user)->create(['name' => 'Chennai Warriors']);

        $this->actingAs($user)->get(route('teams.index'))
            ->assertStatus(200)
            ->assertSee('Chennai Warriors');
    }

    public function test_the_create_team_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('teams.create'))->assertStatus(200);
    }

    public function test_the_edit_team_page_renders(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();

        $this->actingAs($user)->get(route('teams.edit', $team))->assertStatus(200);
    }

    public function test_a_user_can_create_a_team(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/teams', [
            'name' => 'Chennai Warriors',
            'short_name' => 'CHW',
            'city' => 'Chennai',
        ]);

        $team = Team::first();
        $response->assertRedirect(route('teams.show', $team));
        $this->assertSame($user->id, $team->user_id);
    }

    public function test_the_squad_page_lists_players_and_offers_available_ones_to_add(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $inSquadUser = User::factory()->create(['name' => 'Arun Kumar']);
        $availableUser = User::factory()->create(['name' => 'Ravi Shastri']);
        $inSquad = Player::factory()->for($inSquadUser)->create();
        Player::factory()->for($availableUser)->create();
        $team->players()->attach($inSquad);

        $response = $this->actingAs($user)->get(route('teams.show', $team));

        $response->assertStatus(200)
            ->assertSee('Arun Kumar')
            ->assertSee('Ravi Shastri');
    }

    public function test_a_user_cannot_view_another_users_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = Team::factory()->for($owner)->create();

        $this->actingAs($otherUser)->get(route('teams.show', $team))->assertStatus(403);
    }
}
