<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Models\Shop;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_order_with_valid_data()
    {
        // RED: This test should fail initially if Order model has issues
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        
        $orderData = [
            'POS_number' => 'POS-TEST-001',
            'table_number' => 'Table 1',
            'waiter_name' => 'John Waiter',
            'state' => 'preparing',
            'type' => 'dine-in',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 50.00,
            'discount_amount' => 5.00,
            'tax_amount' => 4.50,
            'total_amount' => 49.50,
        ];
        
        $order = Order::create($orderData);
        
        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', $orderData);
        $this->assertEquals('POS-TEST-001', $order->POS_number);
        $this->assertEquals('Table 1', $order->table_number);
        $this->assertEquals('preparing', $order->state);
    }

    /** @test */
    public function it_generates_unique_pos_number_automatically()
    {
        // RED: POS number generation logic needs to be implemented
        $order = Order::factory()->create();
        
        $this->assertNotNull($order->POS_number);
        $this->assertMatchesRegularExpression('/^POS-\d{8}-\d{4}$/', $order->POS_number);
        
        // Test uniqueness
        $order2 = Order::factory()->create();
        $this->assertNotEquals($order->POS_number, $order2->POS_number);
    }

    /** @test */
    public function it_can_calculate_total_amount_from_items()
    {
        // RED: Order total calculation logic needs improvement
        $product = Product::factory()->create(['price' => 10.00]);
        
        $order = Order::factory()->create([
            'subtotal' => 0,
            'total_amount' => 0,
        ]);
        
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 10.00,
            'total_price' => 30.00,
        ]);
        
        $order->refresh();
        
        // This should automatically calculate total from items
        $this->assertEquals(30.00, $order->total_amount);
        $this->assertEquals(30.00, $order->calculateTotal());
    }

    /** @test */
    public function it_transitions_order_state_correctly()
    {
        // RED: State machine logic needs to be implemented
        $order = Order::factory()->create(['state' => 'preparing']);
        
        // Test valid transitions
        $this->assertTrue($order->canTransitionTo('served'));
        $this->assertTrue($order->transitionTo('served'));
        $this->assertEquals('served', $order->state);
        
        // Test invalid transitions
        $this->assertFalse($order->canTransitionTo('preparing'));
        $this->assertFalse($order->transitionTo('preparing'));
        $this->assertEquals('served', $order->state); // Should remain unchanged
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // RED: Validation rules need to be defined
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Order::create([]); // Should fail with missing required fields
    }

    /** @test */
    public function it_has_proper_relationships_with_items()
    {
        // RED: Test relationship implementation
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
        $this->assertEquals($orderItem->id, $order->items->first()->id);
    }

    /** @test */
    public function it_has_proper_relationships_with_customer()
    {
        // RED: Test customer relationship
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        
        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals($customer->id, $order->customer->id);
    }

    /** @test */
    public function it_has_proper_relationships_with_user()
    {
        // RED: Test user relationship
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /** @test */
    public function it_has_proper_relationships_with_shop()
    {
        // RED: Test shop relationship
        $shop = Shop::factory()->create();
        $order = Order::factory()->create(['shop_id' => $shop->id]);
        
        $this->assertInstanceOf(Shop::class, $order->shop);
        $this->assertEquals($shop->id, $order->shop->id);
    }

    /** @test */
    public function it_logs_activity_when_created()
    {
        // RED: Activity logging verification
        $order = Order::factory()->create();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'description' => 'created',
        ]);
    }

    /** @test */
    public function it_can_scope_by_state()
    {
        // RED: Query scope implementation
        $preparingOrder = Order::factory()->create(['state' => 'preparing']);
        $servedOrder = Order::factory()->create(['state' => 'served']);
        $closedOrder = Order::factory()->create(['state' => 'closed']);
        
        $preparingOrders = Order::byState('preparing')->get();
        $servedOrders = Order::byState('served')->get();
        $closedOrders = Order::byState('closed')->get();
        
        $this->assertCount(1, $preparingOrders);
        $this->assertCount(1, $servedOrders);
        $this->assertCount(1, $closedOrders);
        
        $this->assertEquals($preparingOrder->id, $preparingOrders->first()->id);
        $this->assertEquals($servedOrder->id, $servedOrders->first()->id);
        $this->assertEquals($closedOrder->id, $closedOrders->first()->id);
    }

    /** @test */
    public function it_can_scope_by_shop()
    {
        // RED: Shop-based query scope
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        $order1 = Order::factory()->create(['shop_id' => $shop1->id]);
        $order2 = Order::factory()->create(['shop_id' => $shop1->id]);
        $order3 = Order::factory()->create(['shop_id' => $shop2->id]);
        
        $shop1Orders = Order::byShop($shop1->id)->get();
        $shop2Orders = Order::byShop($shop2->id)->get();
        
        $this->assertCount(2, $shop1Orders);
        $this->assertCount(1, $shop2Orders);
    }

    /** @test */
    public function it_handles_order_states_correctly()
    {
        // RED: State validation
        $validStates = ['preparing', 'served', 'closed', 'wastage'];
        
        foreach ($validStates as $state) {
            $order = Order::factory()->create(['state' => $state]);
            $this->assertEquals($state, $order->state);
        }
        
        // Test invalid state
        $this->expectException(\InvalidArgumentException::class);
        Order::factory()->create(['state' => 'invalid_state']);
    }

    /** @test */
    public function it_calculates_order_duration()
    {
        // RED: Duration calculation feature
        $order = Order::factory()->create([
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(15),
        ]);
        
        $duration = $order->getDuration();
        
        $this->assertEquals(15, $duration); // Should return 15 minutes
    }

    /** @test */
    public function it_generates_correct_order_type_validation()
    {
        // RED: Order type validation
        $validTypes = ['dine-in', 'take-away', 'delivery'];
        
        foreach ($validTypes as $type) {
            $order = Order::factory()->create(['type' => $type]);
            $this->assertEquals($type, $order->type);
        }
        
        // Test invalid type
        $this->expectException(\InvalidArgumentException::class);
        Order::factory()->create(['type' => 'invalid_type']);
    }
}