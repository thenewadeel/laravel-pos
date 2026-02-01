<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\OfflineOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OfflineOrderService $service;
    protected User $user;
    protected Shop $shop;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new OfflineOrderService();
        $this->user = User::factory()->create(['type' => 'cashier']);
        $this->shop = Shop::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 10.00,
            'quantity' => 100
        ]);
    }

    /** @test */
    public function it_can_create_an_offline_order_with_pending_sync_status()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 5',
            'waiter_name' => 'John Waiter',
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
            'local_order_id' => 'local-123'
        ];

        $order = $this->service->createOfflineOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending_sync', $order->sync_status);
        $this->assertEquals('preparing', $order->state);
        $this->assertNotNull($order->POS_number);
        $this->assertEquals('Table 5', $order->table_number);
        $this->assertEquals('John Waiter', $order->waiter_name);
        $this->assertEquals(1, $order->items()->count());
    }

    /** @test */
    public function it_queues_offline_order_for_sync()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 3',
            'waiter_name' => 'Jane Waiter',
            'type' => 'take-away',
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
            'local_order_id' => 'local-456'
        ];

        $order = $this->service->createOfflineOrder($orderData);

        $this->assertDatabaseHas('order_sync_queues', [
            'order_id' => $order->id,
            'device_id' => 'tablet-002',
            'local_order_id' => 'local-456',
            'status' => 'pending',
            'sync_type' => 'create'
        ]);
    }

    /** @test */
    public function it_can_process_pending_sync_queue()
    {
        // Create offline order
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
                    'quantity' => 3,
                    'unit_price' => 10.00,
                    'total_price' => 30.00
                ]
            ],
            'subtotal' => 30.00,
            'total_amount' => 30.00,
            'device_id' => 'tablet-003',
            'local_order_id' => 'local-789'
        ];

        $order = $this->service->createOfflineOrder($orderData);
        
        // Process sync
        $result = $this->service->processSyncQueue($order->id);

        $this->assertTrue($result);
        $this->assertEquals('synced', $order->fresh()->sync_status);
        $this->assertDatabaseHas('order_sync_queues', [
            'order_id' => $order->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function it_detects_duplicate_orders_by_local_id()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 2',
            'waiter_name' => 'Duplicate Test',
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
            'device_id' => 'tablet-004',
            'local_order_id' => 'local-dupe-001'
        ];

        // Create first order
        $order1 = $this->service->createOfflineOrder($orderData);
        
        // Try to create duplicate
        $result = $this->service->createOfflineOrder($orderData);

        $this->assertNull($result);
        $this->assertEquals(1, Order::where('local_order_id', 'local-dupe-001')->count());
    }

    /** @test */
    public function it_validates_product_availability_before_creating_order()
    {
        $lowStockProduct = Product::factory()->create([
            'price' => 15.00,
            'quantity' => 2
        ]);

        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 6',
            'waiter_name' => 'Stock Test',
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $lowStockProduct->id,
                    'product_name' => $lowStockProduct->name,
                    'quantity' => 5, // More than available
                    'unit_price' => 15.00,
                    'total_price' => 75.00
                ]
            ],
            'subtotal' => 75.00,
            'total_amount' => 75.00,
            'device_id' => 'tablet-005',
            'local_order_id' => 'local-stock-001'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->service->createOfflineOrder($orderData);
    }

    /** @test */
    public function it_can_get_pending_orders_for_device()
    {
        // Create multiple orders for different devices
        $orderData1 = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table A',
            'waiter_name' => 'Device Test',
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
            'device_id' => 'tablet-device-a',
            'local_order_id' => 'local-device-a-001'
        ];

        $orderData2 = $orderData1;
        $orderData2['local_order_id'] = 'local-device-a-002';
        $orderData2['table_number'] = 'Table B';

        $orderData3 = $orderData1;
        $orderData3['device_id'] = 'tablet-device-b';
        $orderData3['local_order_id'] = 'local-device-b-001';
        $orderData3['table_number'] = 'Table C';

        $this->service->createOfflineOrder($orderData1);
        $this->service->createOfflineOrder($orderData2);
        $this->service->createOfflineOrder($orderData3);

        $pendingOrders = $this->service->getPendingOrdersForDevice('tablet-device-a');

        $this->assertCount(2, $pendingOrders);
        $this->assertTrue($pendingOrders->every(fn($order) => $order->sync_status === 'pending_sync'));
    }

    /** @test */
    public function it_logs_device_sync_activity()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 7',
            'waiter_name' => 'Log Test',
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
            'device_id' => 'tablet-log-001',
            'local_order_id' => 'local-log-001'
        ];

        $order = $this->service->createOfflineOrder($orderData);

        $this->assertDatabaseHas('device_sync_logs', [
            'device_id' => 'tablet-log-001',
            'order_id' => $order->id,
            'action' => 'order_created_offline',
            'status' => 'success'
        ]);
    }

    /** @test */
    public function it_handles_batch_order_upload()
    {
        $ordersData = [
            [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => 'Table 8',
                'waiter_name' => 'Batch 1',
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
                'device_id' => 'tablet-batch-001',
                'local_order_id' => 'local-batch-001'
            ],
            [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => 'Table 9',
                'waiter_name' => 'Batch 2',
                'type' => 'take-away',
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
                'device_id' => 'tablet-batch-001',
                'local_order_id' => 'local-batch-002'
            ]
        ];

        $results = $this->service->processBatchOrders($ordersData);

        $this->assertCount(2, $results['created']);
        $this->assertCount(0, $results['failed']);
        $this->assertEquals(2, Order::where('sync_status', 'pending_sync')->count());
    }
}
