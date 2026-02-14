<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl(),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'aval_status' => $this->faker->boolean(80), // 80% chance of being true
            'kitchen_printer_ip' => $this->faker->ipv4(),
        ];
    }
}