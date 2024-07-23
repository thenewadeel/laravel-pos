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
        $percentages = [40, 50, 20];
        $names = ['MCM Cte', 'Staff', 'Special'];
        $count = count($percentages);
        for ($i = 0; $i < $count; $i++) {
            $randomIndex = rand(0, $count - 1);
            $randomPercentage = $percentages[$randomIndex];
            $randomName = $names[$i];
            Discount::create([
                'name' => $randomName,
                'percentage' => $randomPercentage,
                'amount' => 0,

            ]);
        }
        // 'method', ['NATURAL', 'REVERSE'])->default('NATURAL');
        // $table->enum('type', ['DISCOUNT', 'CHARGES']
        Discount::create([
            'name' => 'Sales.Tax',
            'percentage' => 17.5,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'CHARGES',
        ]);
        Discount::create([
            'name' => 'Service Charges',
            'percentage' => 1,
            'amount' => 500,
            'method' => 'REVERSE',
            'type' => 'CHARGES',
        ]);


        // Seed the pivot table
        // for ($i = 0; $i < 20; $i++) {
        //     $discount = Discount::inRandomOrder()->first();
        //     $product = \App\Models\Product::inRandomOrder()->first();
        //     $discount->products()->attach($product->id);
        // }

        // // Add random discounts to existing orders
        // $orders = \App\Models\Order::all();
        // foreach ($orders as $order) {
        //     $discountIds = Discount::inRandomOrder()->limit(rand(0, 3))->pluck('id')->toArray();
        //     $order->discounts()->sync($discountIds);
        // }
    }
}
