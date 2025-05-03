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

        // 'method', ['NATURAL', 'REVERSE'])->default('NATURAL');
        // $table->enum('type', ['DISCOUNT', 'CHARGES']
        Discount::create([
            'name' => 'Customer',
            'percentage' => 20,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'DISCOUNT',
        ]);
        Discount::create([
            'name' => 'Staff',
            'percentage' => 25,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'DISCOUNT',
        ]);
        Discount::create([
            'name' => 'Special',
            'percentage' => 30,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'DISCOUNT',
        ]);
        Discount::create([
            'name' => 'Service Fee',
            'percentage' => 3,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'CHARGES',
        ]);
        Discount::create([
            'name' => 'Sales Tax',
            'percentage' => 17,
            'amount' => 0,
            'method' => 'NATURAL',
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
