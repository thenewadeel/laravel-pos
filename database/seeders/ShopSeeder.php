<?php

namespace Database\Seeders;

use App\Models\Shop;
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
            "The Charcoal Grill",
            "Fine Dining",
            "Restaurants",
            "Brunch",
            "Coffee Shop",
            "Party",
            "Golfee",
            "Hi Tea",
            "Home Delivery",
            "Lodges",
            "Mid Night Cafe",
            "Mid Night Cafe Brunch",
            "Laundry Shop",
            "Photoshoot",
            "Roof Top",
        ] as $shop) {

            Shop::updateOrCreate([
                'name' => $shop,
                'description' => 'desc',
                'image' => '',
                'status' => true
            ]);
        };
    }
}
