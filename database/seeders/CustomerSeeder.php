<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $Customers = [
            ["Id" => 1, "Title" => "Mr Saillah Paacha"],
            ["Id" => 3, "Title" => "Marker M.K. Mioo"],

        ];
        foreach (array_slice($Customers, 0, 120) as $entry) {

            Customer::updateOrCreate([
                'name' => $entry["Title"],
                'photo' => '',
                'membership_number' => $entry["Id"],
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
            ]);
        };
    }
}
