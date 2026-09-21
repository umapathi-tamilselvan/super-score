<?php

namespace Database\Factories;

use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\PlayingXi;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayingXi>
 */
class PlayingXiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => CricketMatch::factory(),
            'team_id' => Team::factory(),
            'player_id' => Player::factory(),
            'is_captain' => false,
            'is_vice_captain' => false,
            'is_wicket_keeper' => false,
            'is_substitute' => false,
        ];
    }
}
