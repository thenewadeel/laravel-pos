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
        foreach (
            [
                "Take away",
                "Restaurant",
                "Buffet",
                "Coffee Shop",
                "Bakery",
                "Hi Tea",
                "Banquet",
                // "Multimedia",
                // "Sound",
            ] as $shop
        ) {

            $shopRecord = Shop::updateOrCreate([
                'name' => $shop,
                'description' => 'desc',
                'image' => '',
            ]);
            $categories = Category::inRandomOrder()->take(rand(1, 5))->pluck('id')->toArray();
            $shopRecord->categories()->sync($categories);

            $cashiers = User::where('type', 'cashier')
                ->inRandomOrder()
                ->first();


            if ($cashiers) {
                $cashiers->shops()->save($shopRecord);
            }
        };

        User::where('type', '<>', 'cashier')
            ->each(function ($user) {
                $user->shops()->sync(Shop::pluck('id'));
            });
        foreach (
            [
                'Token Shop 1',
                'Token Shop 2',
                'Token Shop 3',
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
