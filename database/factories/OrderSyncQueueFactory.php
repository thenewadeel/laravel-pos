<?php

namespace Database\Factories;

use App\Models\OrderSyncQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderSyncQueueFactory extends Factory
{
    protected $model = OrderSyncQueue::class;

    public function definition()
    {
        return [
            'device_id' => 'device-' . $this->faker->unique()->randomNumber(4),
            'order_data' => json_encode([
                'local_order_id' => 'tablet-order-' . $this->faker->unique()->randomNumber(4),
                'table_number' => 'Table ' . $this->faker->numberBetween(1, 20),
                'waiter_name' => $this->faker->name,
                'type' => $this->faker->randomElement(['dine-in', 'take-away', 'delivery']),
                'items' => [],
                'total_amount' => $this->faker->randomFloat(2, 10, 500),
            ]),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'conflict']),
            'retry_count' => 0,
            'error_message' => null,
            'conflict_data' => null,
            'processed_at' => null,
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'processed_at' => null,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'processed_at' => now(),
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'retry_count' => $this->faker->numberBetween(1, 5),
                'error_message' => $this->faker->sentence,
            ];
        });
    }

    public function conflict()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'conflict',
                'conflict_data' => json_encode([
                    'type' => $this->faker->randomElement(['duplicate_order', 'inventory_mismatch', 'customer_change']),
                    'details' => 'Conflict detected during sync',
                ]),
            ];
        });
    }
}