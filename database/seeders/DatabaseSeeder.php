<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ShopSeeder::class);
        // $this->call(CategorySeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(DiscountSeeder::class);
        if (env('APP_DEBUG')) {
            $this->call(OrdersSeeder::class);
            $this->call(ExpenseSeeder::class);
            $this->call(PaymentSeeder::class);
        }
    }
}
