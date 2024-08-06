<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalOrders = \App\Models\Order::count();
        $affectedOrders = (int)ceil($totalOrders * 1);
        $randomOrders = \App\Models\Order::inRandomOrder()->limit($affectedOrders)->get();

        $randomOrders->each(function ($order, $index) {
            $total = $order->items->sum(function ($orderItem) {
                return $orderItem->price;
            });
            $user = $order->user;
            switch ($index % 3) {
                case 0:
                    $total = rand(0, $total);
                    break;
                case 1:
                    $total = rand(0, $total + 10);
                    break;
                default:
                    $total = rand(0, $total / 2);
                    break;
            }
            $payment = $order->payments()->create([
                'amount' => $total,
                'tip' => 0,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'created_at' => $order->created_at
            ]);
        });
    }
}
