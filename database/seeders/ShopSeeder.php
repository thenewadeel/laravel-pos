<?php

namespace Database\Seeders;

use AliBayat\LaravelCategorizable\Category;
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

        foreach (
            [
                'Token Shop 1',
                'Token Shop 2',
                'Token Shop 3',
                'Token Shop 4',
                'Token Shop 5',
                'Token Shop 6'
            ] as $token_shop
        ) {
            $shopRecord = Shop::updateOrCreate([
                'name' => $token_shop,
                'description' => 'desc',
                'image' => '',
            ]);

            $shopRecord->categories()->sync(Category::where('name', 'tokenisable')->first());
        }
    }
}
