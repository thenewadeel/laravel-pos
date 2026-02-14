<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'image' => $this->faker->optional()->imageUrl(400, 300),
            'printer_ip' => $this->faker->ipv4,
        ];
    }
}