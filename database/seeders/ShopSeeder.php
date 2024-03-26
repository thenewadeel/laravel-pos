<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            "Brunch _ Take away",
            "Restaurant _ Buffet",
            "Coffee  Shop",
            "Bakery",
            "Home  Delivery",
            "The Midnight Cafe",
            "Golf Club",
            "Hi Tea",
            "Photo Shoot",
            "Laundry",
            // "Banquet",
            // "Function",
            // "Club Hall Charges",
            // "Lawn Charges",
            // "Stage",
            // "Multimedia",
            // "Sound",
            "GR Mess",
        ] as $shop) {

            $shopRecord = Shop::updateOrCreate([
                'name' => $shop,
                'description' => 'desc',
                'image' => '',
            ]);

            $randomUser = User::where('type', 'cashier')
                ->inRandomOrder()
                ->first();

            if ($randomUser) {
                $randomUser->shops()->save($shopRecord);
            }
        };
    }
}
