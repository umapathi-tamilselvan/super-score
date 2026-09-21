<?php

namespace Database\Factories;

use App\Enums\MatchFormat;
use App\Enums\MatchStatus;
use App\Models\CricketMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CricketMatch>
 */
class CricketMatchFactory extends Factory
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
            'name' => fake()->city().' T20 Cup',
            'format' => MatchFormat::T20,
            'overs' => 20,
            'date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'time' => '14:00:00',
            'venue' => fake()->city().' Cricket Ground',
            'status' => MatchStatus::Scheduled,
        ];
    }
}
