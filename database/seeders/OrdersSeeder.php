<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

class OrdersSeeder extends Seeder
{
    public function run(Faker $faker): void
    {
        $user = \App\Models\User::all()->random();
        $shop = \App\Models\Shop::all()->random();
        $customer = \App\Models\Customer::all()->random();
        $product = \App\Models\Product::all()->random();

        // Create a loop for 500 orders
        for ($i = 0; $i < 250; $i++) {
            $user = \App\Models\User::inRandomOrder()->first();
            $shop = \App\Models\Shop::inRandomOrder()->first();
            $customer = \App\Models\Customer::inRandomOrder()->first();

            $order = Order::create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
                'customer_id' => $customer->id,
                'table_number' => $faker->randomElement(['1', '2', '3', '4', '5']),
                'waiter_name' => $faker->name,
                'state' => $faker->randomElement(['preparing', 'served', 'closed', 'wastage']),
                'type' => $faker->randomElement(['dine-in', 'take-away', 'delivery']),
                'created_at' => $faker->dateTimeBetween('-7 day', '-1 day'),
                'notes' => $faker->sentence,
            ]);

            // Create 3-5 random products for each order
            $products = Product::all()->random(random_int(1, 5));
            foreach ($products as $product) {
                $qty = random_int(1, 5);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price' => $product->price * $qty,
                    'quantity' => $qty,
                ]);
            }
        }
        // Add random discounts to existing orders
        $orders = \App\Models\Order::all();
        foreach ($orders as $order) {
            $discountIds = Discount::inRandomOrder()->limit(rand(0, 3))->pluck('id')->toArray();
            $order->discounts()->sync($discountIds);
        }
    }
    // {"name":"971 Gajjar Halwa Full","price":1500,"category":"Dessert","make":"Pakistani"}


    // $table->string('name');
    // $table->text('description')->nullable();
    // $table->text('category')->nullable();
    // $table->string('image')->nullable();
    // $table->string('barcode')->unique();
    // $table->decimal('price', 8, 2);
    // $table->boolean('status')->default(true);

    // 'name',
    //         'description',
    //         'image',
    //         'barcode',
    //         'price',
    //         'quantity',
    //         'status',
    //         'category'
};
