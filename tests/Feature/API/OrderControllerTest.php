<?php

namespace Tests\Feature\API;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test: Can list orders via API
     * RED Phase: This should fail initially
     */
    public function test_can_list_orders_via_api()
    {
        $orders = Order::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'POS_number',
                                'table_number',
                                'waiter_name',
                                'type',
                                'status',
                                'total_amount',
                                'customer',
                                'items',
                            ]
                        ],
                        'meta' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page',
                        ]
                    ],
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can get specific order via API
     * RED Phase: This should fail initially
     */
    public function test_can_get_specific_order_via_api()
    {
        $order = Order::factory()->create();

        $response = $this->getJson('/api/v1/orders/' . $order->id);

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
                        'customer',
                        'items',
                        'created_at',
                        'updated_at',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $order->id,
                        'POS_number' => $order->POS_number,
                    ],
                ]);
    }

    /**
     * Test: Can create order via API
     * RED Phase: This should fail initially
     */
    public function test_can_create_order_via_api()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['quantity' => 100]);

        $orderData = [
            'customer_id' => $customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'John Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
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
                        'items',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can update order via API
     * RED Phase: This should fail initially
     */
    public function test_can_update_order_via_api()
    {
        $order = Order::factory()->create([
            'table_number' => 'Table 1',
            'waiter_name' => 'John Waiter',
        ]);

        $updateData = [
            'table_number' => 'Table 2',
            'waiter_name' => 'Jane Waiter',
        ];

        $response = $this->putJson('/api/v1/orders/' . $order->id, $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'table_number',
                        'waiter_name',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $order->id,
                        'table_number' => 'Table 2',
                        'waiter_name' => 'Jane Waiter',
                    ],
                ]);
    }

    /**
     * Test: Can delete order via API
     * RED Phase: This should fail initially
     */
    public function test_can_delete_order_via_api()
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson('/api/v1/orders/' . $order->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                ]);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /**
     * Test: Can get order items via API
     * RED Phase: This should fail initially
     */
    public function test_can_get_order_items_via_api()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
        ]);

        $response = $this->getJson('/api/v1/orders/' . $order->id . '/items');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'product_id',
                            'product_name',
                            'quantity',
                            'unit_price',
                            'total_price',
                        ]
                    ],
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can add item to order via API
     * RED Phase: This should fail initially
     */
    public function test_can_add_item_to_order_via_api()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['quantity' => 100]);

        $itemData = [
            'product_id' => $product->id,
            'quantity' => 3,
        ];

        $response = $this->postJson('/api/v1/orders/' . $order->id . '/items', $itemData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'product_id',
                        'quantity',
                        'unit_price',
                        'total_price',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can update order item via API
     * RED Phase: This should fail initially
     */
    public function test_can_update_order_item_via_api()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $item = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
        ]);

        $updateData = [
            'quantity' => 5,
        ];

        $response = $this->putJson('/api/v1/orders/' . $order->id . '/items/' . $item->id, $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'quantity',
                        'total_price',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $item->id,
                        'quantity' => 5,
                    ],
                ]);
    }

    /**
     * Test: Can delete order item via API
     * RED Phase: This should fail initially
     */
    public function test_can_delete_order_item_via_api()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $item = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2,
        ]);

        $response = $this->deleteJson('/api/v1/orders/' . $order->id . '/items/' . $item->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                ]);

        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }

    /**
     * Test: Returns 404 for non-existent order
     * RED Phase: This should fail initially
     */
    public function test_returns_404_for_nonexistent_order()
    {
        $response = $this->getJson('/api/v1/orders/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Order not found',
                    ],
                ]);
    }

    /**
     * Test: Validates required fields on create
     * RED Phase: This should fail initially
     */
    public function test_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/orders', [
            'table_number' => '',
            'type' => 'invalid-type',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['table_number', 'type']);
    }

    /**
     * Test: Validates order type enum
     * RED Phase: This should fail initially
     */
    public function test_validates_order_type_enum()
    {
        $response = $this->postJson('/api/v1/orders', [
            'table_number' => 'Table 1',
            'type' => 'invalid-type',
            'items' => [],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }
}