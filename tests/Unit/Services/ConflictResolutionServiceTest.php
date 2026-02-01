<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\ConflictResolutionService;
use App\Services\OfflineOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictResolutionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConflictResolutionService $conflictService;
    protected OfflineOrderService $orderService;
    protected User $user;
    protected Shop $shop;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->conflictService = new ConflictResolutionService();
        $this->orderService = new OfflineOrderService();
        $this->user = User::factory()->create(['type' => 'cashier']);
        $this->shop = Shop::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 10.00,
            'quantity' => 100
        ]);
    }

    /** @test */
    public function it_detects_duplicate_orders_by_local_id()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'total_price' => 20.00
                ]
            ],
            'subtotal' => 20.00,
            'total_amount' => 20.00,
            'device_id' => 'tablet-001',
            'local_order_id' => 'local-conflict-001'
        ];

        // Create first order
        $order1 = $this->orderService->createOfflineOrder($orderData);
        $this->assertNotNull($order1);

        // Check for conflicts with same local_order_id
        $conflict = $this->conflictService->detectConflict($orderData);

        $this->assertNotNull($conflict);
        $this->assertEquals('duplicate_order', $conflict['type']);
        $this->assertEquals($order1->id, $conflict['existing_order_id']);
    }

    /** @test */
    public function it_detects_no_conflict_for_new_order()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 2',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                    'total_price' => 10.00
                ]
            ],
            'subtotal' => 10.00,
            'total_amount' => 10.00,
            'device_id' => 'tablet-002',
            'local_order_id' => 'local-new-001'
        ];

        $conflict = $this->conflictService->detectConflict($orderData);

        $this->assertNull($conflict);
    }

    /** @test */
    public function it_resolves_duplicate_by_using_server_version()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 3',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'total_price' => 20.00
                ]
            ],
            'subtotal' => 20.00,
            'total_amount' => 20.00,
            'device_id' => 'tablet-003',
            'local_order_id' => 'local-resolve-001'
        ];

        // Create original order
        $originalOrder = $this->orderService->createOfflineOrder($orderData);
        
        // Resolve conflict using server version
        $result = $this->conflictService->resolveConflict(
            $orderData,
            $originalOrder,
            'use_server'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($originalOrder->id, $result['order_id']);
        $this->assertEquals('server_version_used', $result['resolution']);
    }

    /** @test */
    public function it_resolves_conflict_by_updating_server_version()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 4',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'total_price' => 20.00
                ]
            ],
            'subtotal' => 20.00,
            'total_amount' => 20.00,
            'device_id' => 'tablet-004',
            'local_order_id' => 'local-update-001'
        ];

        // Create original order
        $originalOrder = $this->orderService->createOfflineOrder($orderData);
        
        // Modified data
        $modifiedData = $orderData;
        $modifiedData['items'][0]['quantity'] = 5;
        $modifiedData['items'][0]['total_price'] = 50.00;
        $modifiedData['total_amount'] = 50.00;

        // Resolve by updating server version
        $result = $this->conflictService->resolveConflict(
            $modifiedData,
            $originalOrder,
            'update_server'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(50.00, $originalOrder->fresh()->total_amount);
        $this->assertEquals('server_version_updated', $result['resolution']);
    }

    /** @test */
    public function it_detects_inventory_conflict_when_stock_changed()
    {
        $product2 = Product::factory()->create([
            'price' => 15.00,
            'quantity' => 10
        ]);

        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 5',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $product2->id,
                    'product_name' => $product2->name,
                    'quantity' => 8,
                    'unit_price' => 15.00,
                    'total_price' => 120.00
                ]
            ],
            'subtotal' => 120.00,
            'total_amount' => 120.00,
            'device_id' => 'tablet-005',
            'local_order_id' => 'local-inventory-001'
        ];

        // Reduce stock after order was created offline
        $product2->decrement('quantity', 5); // Now only 5 left

        $conflict = $this->conflictService->detectInventoryConflict($orderData);

        $this->assertNotNull($conflict);
        $this->assertEquals('insufficient_inventory', $conflict['type']);
        $this->assertEquals(5, $conflict['available_quantity']);
        $this->assertEquals(8, $conflict['requested_quantity']);
    }

    /** @test */
    public function it_resolves_inventory_conflict_by_adjusting_quantity()
    {
        $product3 = Product::factory()->create([
            'price' => 20.00,
            'quantity' => 15 // Start with enough stock
        ]);

        // First create order with valid quantity
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 6',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $product3->id,
                    'product_name' => $product3->name,
                    'quantity' => 10,
                    'unit_price' => 20.00,
                    'total_price' => 200.00
                ]
            ],
            'subtotal' => 200.00,
            'total_amount' => 200.00,
            'device_id' => 'tablet-006',
            'local_order_id' => 'local-inventory-resolve-001'
        ];

        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Now simulate stock reduction (e.g., another order used some stock)
        // Remaining stock is 5 (15 - 10 from the order above)
        // But let's say we want to adjust to only 3
        $adjustedItems = [
            [
                'product_id' => $product3->id,
                'product_name' => $product3->name,
                'quantity' => 3, // Adjust down to 3
                'unit_price' => 20.00,
                'total_price' => 60.00
            ]
        ];

        $result = $this->conflictService->resolveInventoryConflict(
            $order,
            $adjustedItems,
            'adjust_quantity'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(60.00, $order->fresh()->total_amount); // 3 * 20
        $this->assertEquals(3, $order->items()->first()->quantity);
    }

    /** @test */
    public function it_gets_conflict_summary_for_device()
    {
        // Create multiple orders with potential conflicts
        $orderData1 = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table A',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                    'total_price' => 10.00
                ]
            ],
            'subtotal' => 10.00,
            'total_amount' => 10.00,
            'device_id' => 'tablet-summary-001',
            'local_order_id' => 'local-summary-001'
        ];

        $this->orderService->createOfflineOrder($orderData1);

        $summary = $this->conflictService->getConflictSummary('tablet-summary-001');

        $this->assertArrayHasKey('total_orders', $summary);
        $this->assertArrayHasKey('conflicts_detected', $summary);
        $this->assertArrayHasKey('resolvable_automatically', $summary);
        $this->assertArrayHasKey('requires_manual_intervention', $summary);
        $this->assertEquals(1, $summary['total_orders']);
    }

    /** @test */
    public function it_can_merge_orders_with_different_items()
    {
        $product2 = Product::factory()->create([
            'price' => 25.00,
            'quantity' => 50
        ]);

        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 7',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'total_price' => 20.00
                ]
            ],
            'subtotal' => 20.00,
            'total_amount' => 20.00,
            'device_id' => 'tablet-007',
            'local_order_id' => 'local-merge-001'
        ];

        $originalOrder = $this->orderService->createOfflineOrder($orderData);

        // New data with additional item
        $newData = $orderData;
        $newData['items'][] = [
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'quantity' => 1,
            'unit_price' => 25.00,
            'total_price' => 25.00
        ];
        $newData['total_amount'] = 45.00;

        $result = $this->conflictService->mergeOrders($originalOrder, $newData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $originalOrder->fresh()->items()->count());
        $this->assertEquals(45.00, $originalOrder->fresh()->total_amount);
    }

    /** @test */
    public function it_auto_resolves_identical_orders()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 8',
            'waiter_name' => 'Test Waiter',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 3,
                    'unit_price' => 10.00,
                    'total_price' => 30.00
                ]
            ],
            'subtotal' => 30.00,
            'total_amount' => 30.00,
            'device_id' => 'tablet-008',
            'local_order_id' => 'local-auto-001'
        ];

        $originalOrder = $this->orderService->createOfflineOrder($orderData);

        // Try to create identical order
        $result = $this->conflictService->autoResolveIfIdentical($orderData, $originalOrder);

        $this->assertTrue($result['auto_resolved']);
        $this->assertEquals('identical_order', $result['reason']);
        $this->assertEquals($originalOrder->id, $result['order_id']);
    }
}
