<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Innings;
use App\Models\Over;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'over_id' => Over::factory(),
            'innings_id' => Innings::factory(),
            'striker_id' => Player::factory(),
            'non_striker_id' => Player::factory(),
            'bowler_id' => Player::factory(),
            'runs_off_bat' => fake()->numberBetween(0, 6),
            'is_legal_delivery' => true,
            'sequence_in_innings' => fake()->unique()->numberBetween(1, 100000),
        ];
    }
}
