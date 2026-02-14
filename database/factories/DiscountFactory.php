<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'percentage' => $this->faker->randomFloat(2, 0, 50),
            'amount' => $this->faker->randomFloat(2, 0, 100),
            'method' => $this->faker->randomElement(['NATURAL', 'REVERSE']),
            'type' => $this->faker->randomElement(['DISCOUNT', 'CHARGES']),
        ];
    }
}
