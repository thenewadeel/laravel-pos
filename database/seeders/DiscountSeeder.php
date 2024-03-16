<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $percentages = [1, 5, 10, 20, 30, 40, 50];
        $names = ['First Discount', 'Second Discount', 'Third Discount', 'Fourth Discount', 'Fifth Discount', 'Sixth Discount', 'Seventh Discount'];
        $count = count($percentages);
        for ($i = 0; $i < $count; $i++) {
            $randomIndex = rand(0, $count - 1);
            $randomPercentage = $percentages[$randomIndex];
            $randomName = $names[$i];
            Discount::create([
                'name' => $randomName,
                'percentage' => $randomPercentage,
                'amount' => rand(0, 1500),
            ]);
        }

        // Seed the pivot table
        for ($i = 0; $i < 20; $i++) {
            $discount = Discount::inRandomOrder()->first();
            $product = \App\Models\Product::inRandomOrder()->first();
            $discount->products()->attach($product->id);
        }
    }
}
