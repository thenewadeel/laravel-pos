<?php

namespace Tests\Unit\Console;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\OfflineOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncOfflineOrdersTest extends TestCase
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
    public function it_shows_sync_status_when_no_options_provided()
    {
        $this->artisan('orders:sync-offline')
            ->assertSuccessful()
            ->expectsOutput('Offline Order Sync Status')
            ->expectsOutput('=========================')
            ->expectsOutput('Total Orders: 0')
            ->expectsOutput('Pending: 0')
            ->expectsOutput('Synced: 0');
    }

    /** @test */
    public function it_can_sync_specific_order()
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
            'device_id' => 'tablet-cmd',
            'local_order_id' => 'local-cmd-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);

        $this->artisan('orders:sync-offline', ['--order' => $order->id])
            ->assertSuccessful()
            ->expectsOutputToContain("Order {$order->id}:")
            ->expectsOutputToContain('✓ Synced successfully');

        $this->assertEquals('synced', $order->fresh()->sync_status);
    }

    /** @test */
    public function it_shows_error_for_nonexistent_order()
    {
        $this->artisan('orders:sync-offline', ['--order' => 99999])
            ->assertFailed()
            ->expectsOutputToContain('Order not found');
    }

    /** @test */
    public function it_can_sync_all_pending_orders_for_device()
    {
        // Create multiple orders
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
                'device_id' => 'tablet-cmd-device',
                'local_order_id' => "local-cmd-{$i}"
            ];
            $this->orderService->createOfflineOrder($orderData);
        }

        $this->artisan('orders:sync-offline', ['--device' => 'tablet-cmd-device'])
            ->assertSuccessful()
            ->expectsOutputToContain('Found 3 pending orders for device: tablet-cmd-device')
            ->expectsOutputToContain('✓ Dispatched sync job');
    }

    /** @test */
    public function it_shows_warning_when_no_pending_orders_for_device()
    {
        $this->artisan('orders:sync-offline', ['--device' => 'nonexistent-device'])
            ->assertSuccessful()
            ->expectsOutputToContain('No pending orders found for device');
    }

    /** @test */
    public function it_can_sync_all_pending_orders_system_wide()
    {
        // Create orders for multiple devices
        $devices = ['tablet-A', 'tablet-B'];
        foreach ($devices as $device) {
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
                'device_id' => $device,
                'local_order_id' => "local-all-{$device}"
            ];
            $this->orderService->createOfflineOrder($orderData);
        }

        $this->artisan('orders:sync-offline', ['--all' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Found 2 pending orders system-wide')
            ->expectsOutputToContain('✓ Dispatched sync job for all pending orders');
    }

    /** @test */
    public function it_supports_dry_run_mode()
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
            'device_id' => 'tablet-dry',
            'local_order_id' => 'local-dry-001'
        ];
        $order = $this->orderService->createOfflineOrder($orderData);

        $this->artisan('orders:sync-offline', [
            '--order' => $order->id,
            '--dry-run' => true
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY RUN] Would sync this order');

        // Verify order was NOT synced
        $this->assertEquals('pending_sync', $order->fresh()->sync_status);
    }
}
