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
    }
}
