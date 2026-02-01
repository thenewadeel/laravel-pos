<?php

namespace Tests\Unit\Livewire;

use App\Livewire\SyncStatusComponent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\OfflineOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SyncStatusComponentTest extends TestCase
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
        $this->user = User::factory()->create(['type' => 'admin']);
        $this->shop = Shop::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 10.00,
            'quantity' => 100,
            'aval_status' => true
        ]);
    }

    /** @test */
    public function it_displays_sync_statistics()
    {
        // Create some orders
        for ($i = 1; $i <= 3; $i++) {
            $orderData = [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => "Table {$i}",
                'waiter_name' => 'Test',
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
                'device_id' => 'tablet-sync-001',
                'local_order_id' => "local-sync-{$i}"
            ];
            $this->orderService->createOfflineOrder($orderData);
        }

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->assertSee('3')
            ->assertSee('tablet-sync-001');
    }

    /** @test */
    public function it_can_filter_by_device()
    {
        // Create orders for different devices
        $devices = ['tablet-A', 'tablet-B'];
        foreach ($devices as $device) {
            $orderData = [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => "Table {$device}",
                'waiter_name' => 'Test',
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
                'device_id' => $device,
                'local_order_id' => "local-{$device}"
            ];
            $this->orderService->createOfflineOrder($orderData);
        }

        $component = Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->set('selectedDevice', 'tablet-A');
        
        // Check that the device stats are filtered
        $stats = $component->get('deviceStats');
        $this->assertEquals(1, $stats['total']); // Only 1 order for tablet-A
    }

    /** @test */
    public function it_shows_pending_orders_count()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'Test',
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
            'device_id' => 'tablet-pending',
            'local_order_id' => 'local-pending-001'
        ];
        $this->orderService->createOfflineOrder($orderData);

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->assertSee('Pending')
            ->assertSee('1');
    }

    /** @test */
    public function it_can_trigger_sync_for_device()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'Test',
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
            'device_id' => 'tablet-trigger',
            'local_order_id' => 'local-trigger-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        $orderId = $order->id;

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->set('selectedDevice', 'tablet-trigger')
            ->call('syncDevice', 'tablet-trigger')
            ->assertDispatched('sync-started');

        // Note: The actual sync happens asynchronously via queued job
        // In production, a queue worker would process this
        // For this test, we just verify the dispatch event was fired
        
        // Verify order still exists and is pending
        $this->assertNotNull(Order::find($orderId));
        $this->assertEquals('pending_sync', Order::find($orderId)->sync_status);
    }

    /** @test */
    public function it_shows_last_sync_time()
    {
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'Test',
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
            'device_id' => 'tablet-time',
            'local_order_id' => 'local-time-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        $this->orderService->processSyncQueue($order->id);

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->assertSee('Last Sync');
    }

    /** @test */
    public function it_shows_failed_syncs()
    {
        // Create a failed sync queue entry
        $orderData = [
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'table_number' => 'Table 1',
            'waiter_name' => 'Test',
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
            'device_id' => 'tablet-failed',
            'local_order_id' => 'local-failed-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Mark sync queue as failed
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'failed',
            'error_message' => 'Network error'
        ]);

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->assertSee('Failed')
            ->assertSee('Network error');
    }

    /** @test */
    public function it_can_refresh_status()
    {
        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->call('refreshStatus')
            ->assertDispatched('status-refreshed');
    }

    /** @test */
    public function it_shows_device_list()
    {
        // Create orders for multiple devices
        $devices = ['tablet-1', 'tablet-2', 'tablet-3'];
        foreach ($devices as $i => $device) {
            $orderData = [
                'shop_id' => $this->shop->id,
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'table_number' => "Table {$i}",
                'waiter_name' => 'Test',
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
                'device_id' => $device,
                'local_order_id' => "local-dev-{$i}"
            ];
            $this->orderService->createOfflineOrder($orderData);
        }

        Livewire::actingAs($this->user)
            ->test(SyncStatusComponent::class)
            ->assertSee('tablet-1')
            ->assertSee('tablet-2')
            ->assertSee('tablet-3');
    }
}
