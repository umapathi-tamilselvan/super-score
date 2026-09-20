<?php

namespace Database\Factories;

use App\Enums\PlayerRole;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date_of_birth' => fake()->date(),
            'role' => fake()->randomElement(PlayerRole::cases()),
            'batting_style' => fake()->randomElement(['Right-hand bat', 'Left-hand bat']),
            'bowling_style' => fake()->randomElement([
                'Right-arm fast', 'Left-arm fast', 'Right-arm off break', 'Left-arm orthodox', null,
            ]),
        ];
    }
}
