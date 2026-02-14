<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'membership_number' => 'MEM-' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+1-555-' . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'address' => $this->faker->address,
        ];
    }
}
