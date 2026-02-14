<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    private static $tableCounter = 1;

    public function definition()
    {
        return [
            'floor_id' => Floor::factory(),
            'table_number' => 'T-' . str_pad(self::$tableCounter++, 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->optional()->randomElement(['Window Table', 'Corner Table', 'Booth', 'High Top']),
            'capacity' => $this->faker->numberBetween(2, 8),
            'status' => $this->faker->randomElement(['available', 'occupied', 'reserved', 'cleaning', 'maintenance']),
            'position_x' => $this->faker->randomFloat(2, 0, 900),
            'position_y' => $this->faker->randomFloat(2, 0, 700),
            'width' => $this->faker->randomFloat(2, 60, 150),
            'height' => $this->faker->randomFloat(2, 60, 150),
            'shape' => $this->faker->randomElement(['rectangle', 'circle', 'oval']),
            'is_active' => true,
            'metadata' => null,
        ];
    }

    public function available()
    {
        return $this->state(function () {
            return [
                'status' => 'available',
            ];
        });
    }

    public function occupied()
    {
        return $this->state(function () {
            return [
                'status' => 'occupied',
            ];
        });
    }

    public function reserved()
    {
        return $this->state(function () {
            return [
                'status' => 'reserved',
            ];
        });
    }

    public function atPosition($x, $y)
    {
        return $this->state(function () use ($x, $y) {
            return [
                'position_x' => $x,
                'position_y' => $y,
            ];
        });
    }
}
