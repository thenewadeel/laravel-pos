<?php

namespace Tests\Unit\Livewire;

use App\Livewire\ConflictResolutionComponent;
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

class ConflictResolutionComponentTest extends TestCase
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
    public function it_displays_conflicts_list()
    {
        // Create a conflict
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
            'device_id' => 'tablet-conflict',
            'local_order_id' => 'local-conflict-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Mark as conflict
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => ['type' => 'duplicate_order', 'message' => 'Order already exists']
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->assertSee('local-conflict-001')
            ->assertSee('duplicate_order');
    }

    /** @test */
    public function it_can_resolve_conflict_using_server_version()
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
            'device_id' => 'tablet-resolve',
            'local_order_id' => 'local-resolve-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => ['type' => 'duplicate_order']
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->call('resolveConflict', $order->id, 'use_server')
            ->assertDispatched('conflict-resolved');
    }

    /** @test */
    public function it_can_resolve_conflict_by_updating_server()
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
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'total_price' => 20.00
                ]
            ],
            'subtotal' => 20.00,
            'total_amount' => 20.00,
            'device_id' => 'tablet-update',
            'local_order_id' => 'local-update-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => ['type' => 'data_mismatch']
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->call('resolveConflict', $order->id, 'update_server')
            ->assertDispatched('conflict-resolved');
    }

    /** @test */
    public function it_can_merge_orders()
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
            'device_id' => 'tablet-merge',
            'local_order_id' => 'local-merge-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => ['type' => 'duplicate_order']
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->call('resolveConflict', $order->id, 'merge')
            ->assertDispatched('conflict-resolved');
    }

    /** @test */
    public function it_shows_conflict_details()
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
                    'quantity' => 5,
                    'unit_price' => 10.00,
                    'total_price' => 50.00
                ]
            ],
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'device_id' => 'tablet-inventory',
            'local_order_id' => 'local-inventory-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Reduce stock to create inventory conflict
        $this->product->update(['quantity' => 2]);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => [
                'type' => 'insufficient_inventory',
                'available_quantity' => 2,
                'requested_quantity' => 5
            ]
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->assertSee('insufficient_inventory');
    }

    /** @test */
    public function it_can_resolve_inventory_conflict_by_adjusting()
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
                    'quantity' => 5,
                    'unit_price' => 10.00,
                    'total_price' => 50.00
                ]
            ],
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'device_id' => 'tablet-inventory-resolve',
            'local_order_id' => 'local-inventory-resolve-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        // Reduce stock
        $this->product->update(['quantity' => 3]);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => [
                'type' => 'insufficient_inventory',
                'available_quantity' => 3,
                'requested_quantity' => 5
            ]
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->call('resolveInventoryConflict', $order->id, 'adjust_quantity')
            ->assertDispatched('conflict-resolved');
    }

    /** @test */
    public function it_can_dismiss_conflict()
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
            'device_id' => 'tablet-dismiss',
            'local_order_id' => 'local-dismiss-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);
        
        OrderSyncQueue::where('order_id', $order->id)->update([
            'status' => 'conflict',
            'conflict_data' => ['type' => 'duplicate_order']
        ]);

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->call('dismissConflict', $order->id)
            ->assertDispatched('conflict-dismissed');
    }

    /** @test */
    public function it_shows_conflict_statistics()
    {
        // Create multiple conflicts
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
                'device_id' => 'tablet-stats',
                'local_order_id' => "local-stats-{$i}"
            ];
            $order = $this->orderService->createOfflineOrder($orderData);
            
            OrderSyncQueue::where('order_id', $order->id)->update([
                'status' => 'conflict',
                'conflict_data' => ['type' => 'duplicate_order']
            ]);
        }

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->assertSee('3')
            ->assertSee('Conflicts');
    }

    /** @test */
    public function it_can_filter_conflicts_by_type()
    {
        // Create different types of conflicts
        $types = ['duplicate_order', 'insufficient_inventory', 'data_mismatch'];
        foreach ($types as $i => $type) {
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
                'device_id' => 'tablet-filter',
                'local_order_id' => "local-filter-{$i}"
            ];
            $order = $this->orderService->createOfflineOrder($orderData);
            
            OrderSyncQueue::where('order_id', $order->id)->update([
                'status' => 'conflict',
                'conflict_data' => ['type' => $type]
            ]);
        }

        Livewire::actingAs($this->user)
            ->test(ConflictResolutionComponent::class)
            ->set('conflictTypeFilter', 'duplicate_order')
            ->assertSee('duplicate_order');
    }
}
