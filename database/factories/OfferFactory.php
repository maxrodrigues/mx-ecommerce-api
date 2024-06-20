<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'code' => fake()->word(),
            'discount' => fake()->numberBetween(1, 100),
            'start_at' => now()->format('Y-m-d H:i:s'),
            'finish_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ];
    }
}
