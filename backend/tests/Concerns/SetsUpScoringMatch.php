<?php

namespace Tests\Concerns;

use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Builds a match with two full (playing-XI-sized) squads and both
 * playing XIs already selected — everything Phase 4's scoring engine
 * needs before an innings can start.
 */
trait SetsUpScoringMatch
{
    protected User $organizer;

    protected Team $teamA;

    protected Team $teamB;

    /** @var Collection<int, Player> */
    protected Collection $playersA;

    /** @var Collection<int, Player> */
    protected Collection $playersB;

    protected CricketMatch $scoringMatch;

    protected function setUpScoringMatch(int $overs = 20): void
    {
        $this->organizer = User::factory()->create();
        $this->teamA = Team::factory()->create(['name' => 'Chennai Warriors']);
        $this->teamB = Team::factory()->create(['name' => 'Tamil Kings']);

        $this->playersA = $this->squadOf($this->teamA);
        $this->playersB = $this->squadOf($this->teamB);

        $this->scoringMatch = CricketMatch::factory()->for($this->organizer)->create(['overs' => $overs]);
        $this->scoringMatch->teams()->sync([
            $this->teamA->id => ['side' => 'team_a'],
            $this->teamB->id => ['side' => 'team_b'],
        ]);

        $this->setPlayingXi($this->teamA, $this->playersA);
        $this->setPlayingXi($this->teamB, $this->playersB);
    }

    /**
     * @return Collection<int, Player>
     */
    private function squadOf(Team $team): Collection
    {
        $players = Player::factory()->count(config('cricket.playing_xi_size'))->create();
        $team->players()->attach($players->pluck('id'));

        return $players;
    }

    /**
     * @param  Collection<int, Player>  $players
     */
    private function setPlayingXi(Team $team, Collection $players): void
    {
        $rows = $players->map(fn (Player $player, int $index) => [
            'match_id' => $this->scoringMatch->id,
            'team_id' => $team->id,
            'player_id' => $player->id,
            'is_captain' => $index === 0,
            'is_vice_captain' => false,
            'is_wicket_keeper' => $index === 1,
            'is_substitute' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        $this->scoringMatch->playingXi()->insert($rows);
    }

    private function tokenForOrganizer(): string
    {
        return $this->organizer->createToken('test')->plainTextToken;
    }
}
