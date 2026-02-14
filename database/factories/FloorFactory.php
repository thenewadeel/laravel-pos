<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition()
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => $this->faker->randomElement(['Ground Floor', 'First Floor', 'Terrace', 'VIP Section', 'Outdoor']),
            'description' => $this->faker->sentence,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
            'layout_config' => [
                'width' => 1000,
                'height' => 800,
                'background' => 'default',
            ],
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
