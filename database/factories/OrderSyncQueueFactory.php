<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderSyncQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderSyncQueueFactory extends Factory
{
    protected $model = OrderSyncQueue::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'device_id' => 'device-' . $this->faker->unique()->randomNumber(4),
            'local_order_id' => 'tablet-order-' . $this->faker->unique()->randomNumber(4),
            'sync_type' => $this->faker->randomElement(['create', 'update', 'delete']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'conflict']),
            'retry_count' => 0,
            'error_message' => null,
            'conflict_data' => null,
            'last_attempt_at' => null,
            'completed_at' => null,
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'last_attempt_at' => null,
                'completed_at' => null,
            ];
        });
    }

    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'last_attempt_at' => now(),
                'completed_at' => null,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => now(),
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
                'conflict_data' => [
                    'type' => $this->faker->randomElement(['duplicate_order', 'inventory_mismatch', 'customer_change']),
                    'details' => 'Conflict detected during sync',
                ],
            ];
        });
    }
}
