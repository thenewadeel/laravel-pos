<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrdersSeeder extends Seeder
{
    private $BATCHES = 10;
    private $ORDERS_IN_BATCH = 200;
    public function run(Faker $faker): void
    {
        $user = \App\Models\User::all()->random();
        $shop = \App\Models\Shop::all()->random();
        $customer = \App\Models\Customer::all()->random();
        $products = Product::all();

        for ($batch = 0; $batch < $this->BATCHES; $batch++) {
            $orders = [];
            Log::notice("Batching order seeder $batch");
            // Create a loop for 1000 orders
            for ($i = 0; $i < $this->ORDERS_IN_BATCH; $i++) {
                $user = \App\Models\User::inRandomOrder()->first();
                $shop = \App\Models\Shop::inRandomOrder()->first();
                $customer = \App\Models\Customer::inRandomOrder()->first();
                $creation_time = $faker->dateTimeBetween('-15 day', '+6 day');
                $orders[] =
                    [
                        'user_id' => $user->id,
                        'shop_id' => $shop->id,
                        'customer_id' => $customer->id,
                        'table_number' => $faker->randomElement(['1', '2', '3', '4', '5']),
                        'waiter_name' => $faker->name,
                        'state' => $faker->randomElement(['preparing', 'closed', 'wastage']),
                        'type' => $faker->randomElement(['dine-in', 'take-away', 'delivery']),
                        'created_at' => $creation_time,
                        'notes' => $faker->sentence,
                        'POS_number' => sprintf('%04d', $i + 1) . '-' . $creation_time->format('d-m-Y'),
                    ];
                // if (random_int(1, 100) <= 70) {
                //     $order->assignPOS();
                // }
            }
            Order::insert($orders);


            // Create 3-5 random products for each order
            $orders = Order::orderByDesc('id')->limit($this->ORDERS_IN_BATCH)->get();
            //inRandomOrder()->limit(random_int(1, 5));
            // for ($j = 0; $j < 25000; $j++) {
            foreach ($orders as  $order) {
                $randomProducts = $products->random(random_int(3, 5));
                $orderItems = [];
                foreach ($randomProducts as $product) {
                    $qty = random_int(1, 5);
                    $orderItems[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_rate' => $product->price,
                        'price' => $product->price * $qty,
                        'quantity' => $qty,
                        'created_at' => $order->created_at
                    ];
                }
                OrderItem::insert($orderItems);

                if (rand(1, 10) < 2) {
                    $discountIds = Discount::inRandomOrder()->limit(rand(0, 3))->pluck('id')->toArray();
                    $order->discounts()->sync($discountIds);
                }
            }
            // Add random discounts to existing orders
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
