<?php

namespace Database\Factories;

use App\Enums\InningsStatus;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Innings>
 */
class InningsFactory extends Factory
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
            'innings_number' => 1,
            'status' => InningsStatus::InProgress,
        ];
    }
}
