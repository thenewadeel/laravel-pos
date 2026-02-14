<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 1000,
            'quantity' => 100
        ]);
        $this->customer = Customer::factory()->create();
    }

    public function test_user_can_create_order(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/orders', [
                'customer_id' => $this->customer->id,
                'table' => 'Table 1',
                'type' => 'dine-in',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'price' => $this->product->price
                    ]
                ]
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'type' => 'dine-in'
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price * 2
        ]);
    }

    public function test_order_updates_product_quantity(): void
    {
        $initialQuantity = $this->product->quantity;
        
        $this->actingAs($this->user)
            ->post('/orders', [
                'customer_id' => $this->customer->id,
                'table' => 'Table 1',
                'type' => 'dine-in',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 3,
                        'price' => $this->product->price
                    ]
                ]
            ]);

        $this->product->refresh();
        $this->assertEquals($initialQuantity - 3, $this->product->quantity);
    }

    public function test_cannot_order_insufficient_stock(): void
    {
        $this->product->update(['quantity' => 1]);

        $response = $this->actingAs($this->user)
            ->post('/orders', [
                'customer_id' => $this->customer->id,
                'table' => 'Table 1',
                'type' => 'dine-in',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'price' => $this->product->price
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_can_view_order_list(): void
    {
        Order::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get('/orders');

        $response->assertOk();
        $response->assertViewHas('orders');
    }

    public function test_can_update_order_status(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'state' => 'preparing'
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/orders/{$order->id}/status", [
                'state' => 'served'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'state' => 'served'
        ]);
    }
}