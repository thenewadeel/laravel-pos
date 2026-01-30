<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'POS_number' => 'POS-' . $this->faker->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'shop_id' => Shop::factory(),
            'user_id' => User::factory(),
            'state' => $this->faker->randomElement(['preparing', 'served', 'wastage', 'closed']),
            'type' => $this->faker->randomElement(['dine-in', 'take-away', 'delivery']),
            'table_number' => $this->faker->bothify('Table ##'),
            'waiter_name' => $this->faker->name(),
            'notes' => $this->faker->sentence(),
            'subtotal' => $this->faker->randomFloat(2, 0, 1000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'tax_amount' => $this->faker->randomFloat(2, 0, 50),
            'total_amount' => $this->faker->randomFloat(2, 0, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}