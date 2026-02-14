<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DeviceSyncLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfflineSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating offline sync test data...');

        // Get or create required base data
        $shop = Shop::first() ?? Shop::factory()->create();
        $user = User::where('type', 'cashier')->first() ?? User::factory()->create(['type' => 'cashier']);
        $customer = Customer::first() ?? Customer::factory()->create();
        $products = Product::where('aval_status', true)->limit(5)->get();

        if ($products->isEmpty()) {
            $products = Product::factory()->count(5)->create(['aval_status' => true, 'quantity' => 100]);
        }

        // Create sample devices
        $devices = [
            'tablet-floor-1',
            'tablet-floor-2',
            'tablet-outdoor',
            'tablet-bar',
        ];

        // Create pending orders
        $this->createPendingOrders($devices, $shop, $user, $customer, $products);

        // Create synced orders
        $this->createSyncedOrders($devices, $shop, $user, $customer, $products);

        // Create orders with conflicts
        $this->createConflictOrders($devices, $shop, $user, $customer, $products);

        // Create failed orders
        $this->createFailedOrders($devices, $shop, $user, $customer, $products);

        $this->command->info('Offline sync test data created successfully!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('  - Pending orders: ' . Order::where('sync_status', 'pending_sync')->count());
        $this->command->info('  - Synced orders: ' . Order::where('sync_status', 'synced')->count());
        $this->command->info('  - Conflicts: ' . OrderSyncQueue::where('status', 'conflict')->count());
        $this->command->info('  - Failed: ' . OrderSyncQueue::where('status', 'failed')->count());
        $this->command->info('');
        $this->command->info('Access the offline sync features at:');
        $this->command->info('  - Tablet Order: /tablet-order');
        $this->command->info('  - Sync Status: /sync-status');
        $this->command->info('  - Conflict Resolution: /conflict-resolution');
    }

    /**
     * Create pending orders
     */
    private function createPendingOrders(array $devices, Shop $shop, User $user, Customer $customer, $products): void
    {
        $this->command->info('Creating pending orders...');

        foreach ($devices as $index => $device) {
            // Create 2-3 pending orders per device
            for ($i = 1; $i <= rand(2, 3); $i++) {
                $order = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'table_number' => "Table {$device}-{$i}",
                    'waiter_name' => $user->first_name,
                    'type' => 'dine-in',
                    'state' => 'preparing',
                    'sync_status' => 'pending_sync',
                    'local_order_id' => "local-{$device}-pending-{$i}",
                    'device_id' => $device,
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]);

                // Add items
                $total = 0;
                $selectedProducts = $products->random(rand(1, 3));
                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $itemTotal = $product->price * $quantity;
                    $total += $itemTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'unit_price' => $product->price,
                        'total_price' => $itemTotal,
                    ]);

                    // Decrement stock
                    $product->decrement('quantity', $quantity);
                }

                $order->update([
                    'subtotal' => $total,
                    'total_amount' => $total,
                ]);

                // Create sync queue entry
                OrderSyncQueue::create([
                    'order_id' => $order->id,
                    'device_id' => $device,
                    'local_order_id' => $order->local_order_id,
                    'sync_type' => 'create',
                    'status' => 'pending',
                ]);

                // Log creation
                DeviceSyncLog::create([
                    'device_id' => $device,
                    'order_id' => $order->id,
                    'action' => 'order_created_offline',
                    'status' => 'success',
                    'details' => json_encode([
                        'table_number' => $order->table_number,
                        'total_amount' => $order->total_amount,
                        'items_count' => $selectedProducts->count(),
                    ]),
                ]);
            }
        }
    }

    /**
     * Create synced orders
     */
    private function createSyncedOrders(array $devices, Shop $shop, User $user, Customer $customer, $products): void
    {
        $this->command->info('Creating synced orders...');

        foreach ($devices as $device) {
            // Create 1-2 synced orders per device
            for ($i = 1; $i <= rand(1, 2); $i++) {
                $order = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'table_number' => "Table {$device}-synced-{$i}",
                    'waiter_name' => $user->first_name,
                    'type' => 'dine-in',
                    'state' => 'preparing',
                    'sync_status' => 'synced',
                    'local_order_id' => "local-{$device}-synced-{$i}",
                    'device_id' => $device,
                    'synced_at' => now()->subHours(rand(1, 24)),
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]);

                // Add items
                $total = 0;
                $selectedProducts = $products->random(rand(1, 3));
                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $itemTotal = $product->price * $quantity;
                    $total += $itemTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'unit_price' => $product->price,
                        'total_price' => $itemTotal,
                    ]);

                    $product->decrement('quantity', $quantity);
                }

                $order->update([
                    'subtotal' => $total,
                    'total_amount' => $total,
                ]);

                // Create completed sync queue entry
                OrderSyncQueue::create([
                    'order_id' => $order->id,
                    'device_id' => $device,
                    'local_order_id' => $order->local_order_id,
                    'sync_type' => 'create',
                    'status' => 'completed',
                    'completed_at' => $order->synced_at,
                ]);

                // Log sync
                DeviceSyncLog::create([
                    'device_id' => $device,
                    'order_id' => $order->id,
                    'action' => 'order_synced',
                    'status' => 'success',
                    'details' => json_encode([
                        'sync_type' => 'create',
                        'retry_count' => 0,
                    ]),
                ]);
            }
        }
    }

    /**
     * Create orders with conflicts
     */
    private function createConflictOrders(array $devices, Shop $shop, User $user, Customer $customer, $products): void
    {
        $this->command->info('Creating orders with conflicts...');

        $conflictTypes = [
            'duplicate_order',
            'insufficient_inventory',
            'data_mismatch',
        ];

        foreach (array_slice($devices, 0, 2) as $device) {
            foreach ($conflictTypes as $conflictType) {
                $order = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'table_number' => "Table {$device}-conflict-{$conflictType}",
                    'waiter_name' => $user->first_name,
                    'type' => 'dine-in',
                    'state' => 'preparing',
                    'sync_status' => 'pending_sync',
                    'local_order_id' => "local-{$device}-conflict-{$conflictType}",
                    'device_id' => $device,
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]);

                // Add items
                $total = 0;
                $selectedProducts = $products->random(rand(1, 2));
                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 2);
                    $itemTotal = $product->price * $quantity;
                    $total += $itemTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'unit_price' => $product->price,
                        'total_price' => $itemTotal,
                    ]);

                    $product->decrement('quantity', $quantity);
                }

                $order->update([
                    'subtotal' => $total,
                    'total_amount' => $total,
                ]);

                // Create conflict sync queue entry
                $conflictData = [
                    'type' => $conflictType,
                    'message' => "Conflict detected: {$conflictType}",
                ];

                if ($conflictType === 'insufficient_inventory') {
                    $conflictData['available_quantity'] = 2;
                    $conflictData['requested_quantity'] = 5;
                } elseif ($conflictType === 'duplicate_order') {
                    $conflictData['existing_order_id'] = rand(1, 100);
                }

                OrderSyncQueue::create([
                    'order_id' => $order->id,
                    'device_id' => $device,
                    'local_order_id' => $order->local_order_id,
                    'sync_type' => 'create',
                    'status' => 'conflict',
                    'conflict_data' => $conflictData,
                ]);

                // Log conflict
                DeviceSyncLog::create([
                    'device_id' => $device,
                    'order_id' => $order->id,
                    'action' => 'conflict_detected',
                    'status' => 'conflict',
                    'details' => json_encode($conflictData),
                ]);
            }
        }
    }

    /**
     * Create failed orders
     */
    private function createFailedOrders(array $devices, Shop $shop, User $user, Customer $customer, $products): void
    {
        $this->command->info('Creating failed orders...');

        foreach (array_slice($devices, 0, 2) as $device) {
            $order = Order::create([
                'shop_id' => $shop->id,
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'table_number' => "Table {$device}-failed",
                'waiter_name' => $user->first_name,
                'type' => 'dine-in',
                'state' => 'preparing',
                'sync_status' => 'pending_sync',
                'local_order_id' => "local-{$device}-failed",
                'device_id' => $device,
                'subtotal' => 0,
                'total_amount' => 0,
            ]);

            // Add items
            $total = 0;
            $selectedProducts = $products->random(1);
            foreach ($selectedProducts as $product) {
                $quantity = 1;
                $itemTotal = $product->price * $quantity;
                $total += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                ]);

                $product->decrement('quantity', $quantity);
            }

            $order->update([
                'subtotal' => $total,
                'total_amount' => $total,
            ]);

            // Create failed sync queue entry
            OrderSyncQueue::create([
                'order_id' => $order->id,
                'device_id' => $device,
                'local_order_id' => $order->local_order_id,
                'sync_type' => 'create',
                'status' => 'failed',
                'retry_count' => 3,
                'error_message' => 'Network timeout - max retries exceeded',
            ]);

            // Log failure
            DeviceSyncLog::create([
                'device_id' => $device,
                'order_id' => $order->id,
                'action' => 'order_sync_failed',
                'status' => 'failed',
                'details' => 'Network timeout - max retries exceeded',
            ]);
        }
    }
}
