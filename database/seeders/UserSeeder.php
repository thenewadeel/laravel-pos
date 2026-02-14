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
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@wt.pos'],
            [
                'first_name' => 'Zia',
                'last_name' => 'Khan',
                'type' => 'admin',
                'password' => bcrypt('admin123')
            ]
        );

        // Create all user types for comprehensive testing
        $users = [
            // Manager
            [
                'email' => 'manager@wt.pos',
                'first_name' => 'Manager',
                'last_name' => 'User',
                'type' => 'admin', // Using admin as manager
                'password' => 'manager123'
            ],
            // Cashier
            [
                'email' => 'cashier@wt.pos',
                'first_name' => 'Cashier',
                'last_name' => 'User',
                'type' => 'cashier',
                'password' => 'cashier123'
            ],
            // Accountant
            [
                'email' => 'accountant@wt.pos',
                'first_name' => 'Accountant',
                'last_name' => 'User',
                'type' => 'accountant',
                'password' => 'accountant123'
            ],
            // Chef
            [
                'email' => 'chef@wt.pos',
                'first_name' => 'Chef',
                'last_name' => 'User',
                'type' => 'chef',
                'password' => 'chef123'
            ],
            // Stock Boy
            [
                'email' => 'stock@wt.pos',
                'first_name' => 'Stock',
                'last_name' => 'Manager',
                'type' => 'stockBoy',
                'password' => 'stock123'
            ],
            // Waiter
            [
                'email' => 'waiter@wt.pos',
                'first_name' => 'Waiter',
                'last_name' => 'User',
                'type' => 'cashier', // Using cashier as waiter for now
                'password' => 'waiter123'
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'type' => $userData['type'],
                    'password' => bcrypt($userData['password'])
                ]
            );
        }
    }
}
