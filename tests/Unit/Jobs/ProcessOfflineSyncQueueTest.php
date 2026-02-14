<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessOfflineSyncQueue;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\OfflineOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessOfflineSyncQueueTest extends TestCase
{
    use RefreshDatabase;

    protected OfflineOrderService $orderService;
    protected User $user;
    protected Shop $shop;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
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
    public function it_processes_single_order_sync()
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
            'device_id' => 'tablet-job-001',
            'local_order_id' => 'local-job-001'
        ];

        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Verify order is pending
        $this->assertEquals('pending_sync', $order->sync_status);

        // Process the sync queue directly (bypassing job for simplicity)
        $result = $this->orderService->processSyncQueue($order->id);

        // Verify order is now synced
        $this->assertTrue($result);
        $this->assertEquals('synced', $order->fresh()->sync_status);
        $this->assertNotNull($order->fresh()->synced_at);
    }

    /** @test */
    public function it_processes_all_pending_orders_for_device()
    {
        // Create multiple orders with unique local_order_ids
        $orders = [];
        for ($i = 1; $i <= 3; $i++) {
            $orderData = [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => "Table {$i}",
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
                'device_id' => 'tablet-device-job',
                'local_order_id' => "local-device-job-{$i}"
            ];

            $orders[] = $this->orderService->createOfflineOrder($orderData);
        }

        // Verify all are pending
        $this->assertEquals(3, Order::where('sync_status', 'pending_sync')->count());

        // Process each order
        foreach ($orders as $order) {
            $this->orderService->processSyncQueue($order->id);
        }

        // Verify all are now synced
        $this->assertEquals(0, Order::where('sync_status', 'pending_sync')->count());
        $this->assertEquals(3, Order::where('sync_status', 'synced')->count());
    }

    /** @test */
    public function it_prevents_duplicate_orders_by_local_id()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table Dup',
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
            'device_id' => 'tablet-dup-001',
            'local_order_id' => 'local-dup-test-001'
        ];

        // Create first order
        $order1 = $this->orderService->createOfflineOrder($orderData);
        $this->assertNotNull($order1);

        // Try to create second order with same local_order_id
        $order2 = $this->orderService->createOfflineOrder($orderData);
        
        // This should return null because duplicate is detected
        $this->assertNull($order2);
        
        // Verify only one order exists
        $this->assertEquals(1, Order::where('local_order_id', 'local-dup-test-001')->count());
    }

    /** @test */
    public function it_tracks_sync_queue_status()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table Queue',
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
            'device_id' => 'tablet-queue-001',
            'local_order_id' => 'local-queue-001'
        ];

        $order = $this->orderService->createOfflineOrder($orderData);
        
        $syncQueue = OrderSyncQueue::where('order_id', $order->id)->first();
        $this->assertNotNull($syncQueue);
        $this->assertEquals('pending', $syncQueue->status);

        // Process the sync
        $this->orderService->processSyncQueue($order->id);

        // Verify queue is completed
        $syncQueue->refresh();
        $this->assertEquals('completed', $syncQueue->status);
        $this->assertNotNull($syncQueue->completed_at);
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        Queue::fake();

        ProcessOfflineSyncQueue::dispatch(123);

        Queue::assertPushed(ProcessOfflineSyncQueue::class);
    }

    /** @test */
    public function it_creates_device_sync_logs()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table Log',
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
            'device_id' => 'tablet-log-job',
            'local_order_id' => 'local-log-job-001'
        ];

        $order = $this->orderService->createOfflineOrder($orderData);

        // Verify creation log exists
        $this->assertDatabaseHas('device_sync_logs', [
            'device_id' => 'tablet-log-job',
            'action' => 'order_created_offline',
            'status' => 'success'
        ]);

        // Process the sync
        $this->orderService->processSyncQueue($order->id);

        // Verify sync log was created
        $this->assertDatabaseHas('device_sync_logs', [
            'device_id' => 'tablet-log-job',
            'order_id' => $order->id,
            'action' => 'order_synced',
            'status' => 'success'
        ]);
    }
}
