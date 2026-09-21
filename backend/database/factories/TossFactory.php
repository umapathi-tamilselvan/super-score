<?php

namespace Database\Factories;

use App\Enums\TossDecision;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Toss;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Toss>
 */
class TossFactory extends Factory
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
            'winner_team_id' => Team::factory(),
            'decision' => fake()->randomElement(TossDecision::cases()),
        ];
    }
}
