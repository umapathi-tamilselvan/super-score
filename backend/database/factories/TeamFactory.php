<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city().' '.fake()->randomElement(['Warriors', 'Kings', 'Strikers', 'Titans', 'CC']);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'short_name' => strtoupper(fake()->lexify('???')),
            'city' => fake()->city(),
        ];
    }
}
