<?php

namespace Database\Factories;

use App\Models\Innings;
use App\Models\Over;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Over>
 */
class OverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'innings_id' => Innings::factory(),
            'over_number' => 1,
            'bowler_id' => Player::factory(),
        ];
    }
}
