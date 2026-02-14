<?php

namespace Tests\Feature\Vue;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $order;
    protected $shop;
    protected $categories;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data - use valid user type from ENUM
        $this->user = User::factory()->create(['type' => 'cashier']);
        $this->shop = Shop::factory()->create();
        $this->user->shops()->attach($this->shop);
        
        // Create categories (products are not required for workspace test)
        $this->categories = Category::factory()->count(3)->create();
        
        // Create some products for potential use
        Product::factory()->count(5)->create([
            'aval_status' => true,
            'quantity' => 100
        ]);
        
        // Create order
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
            'state' => 'preparing'
        ]);
        
        // Add items to order
        $product = Product::factory()->create();
        $this->order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
            'price' => $product->price,
            'product_name' => $product->name,
            'product_rate' => $product->price
        ]);
        
        // Create discounts
        Discount::factory()->count(3)->create();
        
        // Create customers
        Customer::factory()->count(5)->create();
    }

    /**
     * Test: Workspace page loads successfully
     */
    public function test_workspace_page_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('orders.workspace', $this->order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.vue.workspace');
        $response->assertViewHas(['order', 'categories', 'discounts', 'customers']);
    }

    /**
     * Test: API returns order details
     */
    public function test_api_returns_order_details()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/orders/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'POS_number',
                    'table_number',
                    'waiter_name',
                    'type',
                    'status',
                    'total_amount',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'unit_price',
                            'total_price'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test: API creates new order
     */
    public function test_api_creates_new_order()
    {
        $product = Product::factory()->create(['quantity' => 100]);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/orders', [
                'table_number' => 'Table 5',
                'waiter_name' => 'John Waiter',
                'type' => 'dine-in',
                'shop_id' => $this->shop->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2
                    ]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'table_number' => 'Table 5',
                    'waiter_name' => 'John Waiter',
                    'type' => 'dine-in',
                ]
            ]);

        // Check product quantity was decremented
        $this->assertEquals(98, $product->fresh()->quantity);
    }

    /**
     * Test: API adds item to order
     */
    public function test_api_adds_item_to_order()
    {
        $product = Product::factory()->create([
            'price' => 50.00,
            'quantity' => 100
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/orders/{$this->order->id}/items", [
                'product_id' => $product->id,
                'quantity' => 3
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 50.00,
                    'total_price' => 150.00
                ]
            ]);

        // Check order total was updated
        $this->order->refresh();
        $this->assertGreaterThan(0, $this->order->total_amount);
    }

    /**
     * Test: API updates item quantity
     */
    public function test_api_updates_item_quantity()
    {
        $item = $this->order->items()->first();
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/orders/{$this->order->id}/items/{$item->id}", [
                'quantity' => 5
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 5
                ]
            ]);
    }

    /**
     * Test: API deletes item from order
     */
    public function test_api_deletes_item_from_order()
    {
        $item = $this->order->items()->first();
        $product = Product::find($item->product_id);
        $originalQty = $product->quantity;
        
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/orders/{$this->order->id}/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Check product quantity was restored
        $this->assertEquals($originalQty + $item->quantity, $product->fresh()->quantity);
    }

    /**
     * Test: API updates order details
     */
    public function test_api_updates_order_details()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/orders/{$this->order->id}", [
                'table_number' => 'Table 99',
                'waiter_name' => 'Jane Waiter',
                'type' => 'take-away'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'table_number' => 'Table 99',
                    'waiter_name' => 'Jane Waiter'
                ]
            ]);

        $this->order->refresh();
        $this->assertEquals('Table 99', $this->order->table_number);
        $this->assertEquals('take-away', $this->order->type);
    }

    /**
     * Test: Closed order cannot be edited
     */
    public function test_closed_order_cannot_be_edited()
    {
        $this->order->update(['state' => 'closed']);
        
        $response = $this->actingAs($this->user)
            ->get(route('orders.workspace', $this->order));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Order is already closed');
    }

    /**
     * Test: Workspace requires authentication
     */
    public function test_workspace_requires_authentication()
    {
        $response = $this->get(route('orders.workspace', $this->order));
        $response->assertRedirect('/login');
    }
}