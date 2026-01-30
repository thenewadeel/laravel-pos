<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $quantity = $this->faker->numberBetween(1, 10);
        $totalPrice = $unitPrice * $quantity;

        return [
            'price' => $totalPrice,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'quantity' => $quantity,
            'product_id' => Product::factory(),
            'order_id' => Order::factory(),
            'product_name' => $this->faker->word(),
            'product_rate' => $unitPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}