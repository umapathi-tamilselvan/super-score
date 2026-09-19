<?php

namespace Database\Factories;

use App\Enums\OtpPurpose;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OtpCode>
 */
class OtpCodeFactory extends Factory
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
            'code' => fake()->numerify('######'),
            'purpose' => OtpPurpose::Registration,
            'expires_at' => now()->addMinutes(10),
            'consumed_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'consumed_at' => now(),
        ]);
    }
}
