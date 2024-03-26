<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate([
            'email' => 'admin@gmail.com',
            'first_name' => 'Admin',
            'last_name' => 'admin',
            'type' => 'admin',
            'password' => bcrypt('admin123')
        ]);

        // Make 3 cashiers
        $types = ['cashier', 'cashier', 'cashier'];
        $firstNames = ['John', 'Emma', 'David'];
        $lastNames = ['Doe', 'Smith', 'Johnson'];
        $emails = ['john@example.com', 'emma@example.com', 'david@example.com'];
        $passwords = ['password123', 'password123', 'password123'];

        foreach (range(0, 2) as $index) {
            User::updateOrCreate([
                'email' => $emails[$index],
                'first_name' => $firstNames[$index],
                'last_name' => $lastNames[$index],
                'type' => $types[$index],
                'password' => bcrypt($passwords[$index])
            ]);
        }
    }
}
