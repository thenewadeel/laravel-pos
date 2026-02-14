<?php

namespace App\Services;

use App\Models\DeviceSyncLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineOrderService
{
    /**
     * Create an offline order with pending sync status
     *
     * @param array $orderData
     * @return Order|null
     * @throws \Exception
     */
    public function createOfflineOrder(array $orderData): ?Order
    {
        // Check for duplicate local_order_id
        if (isset($orderData['local_order_id'])) {
            $existingOrder = Order::where('local_order_id', $orderData['local_order_id'])->first();
            if ($existingOrder) {
                Log::info('Duplicate offline order detected', [
                    'local_order_id' => $orderData['local_order_id'],
                    'existing_order_id' => $existingOrder->id,
                ]);
                return null;
            }
        }

        // Validate product availability
        $this->validateProductAvailability($orderData['items'] ?? []);

        return DB::transaction(function () use ($orderData) {
            // Create order
            $order = Order::create([
                'shop_id' => $orderData['shop_id'] ?? 1,
                'user_id' => $orderData['user_id'] ?? Auth::id() ?? 1,
                'customer_id' => $orderData['customer_id'] ?? null,
                'table_number' => $orderData['table_number'] ?? null,
                'waiter_name' => $orderData['waiter_name'] ?? null,
                'type' => $orderData['type'] ?? 'dine-in',
                'state' => 'preparing',
                'sync_status' => 'pending_sync',
                'local_order_id' => $orderData['local_order_id'] ?? null,
                'device_id' => $orderData['device_id'] ?? null,
                'subtotal' => $orderData['subtotal'] ?? 0,
                'total_amount' => $orderData['total_amount'] ?? 0,
            ]);

            // Create order items
            foreach ($orderData['items'] ?? [] as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['unit_price'] ?? 0, // Required field
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'total_price' => $itemData['total_price'] ?? 0,
                ]);

                // Decrement product quantity
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->decrement('quantity', $itemData['quantity']);
                }
            }

            // Create sync queue entry
            OrderSyncQueue::create([
                'order_id' => $order->id,
                'device_id' => $orderData['device_id'] ?? 'unknown',
                'local_order_id' => $orderData['local_order_id'] ?? null,
                'sync_type' => 'create',
                'status' => 'pending',
            ]);

            // Log sync activity
            DeviceSyncLog::create([
                'device_id' => $orderData['device_id'] ?? 'unknown',
                'order_id' => $order->id,
                'action' => 'order_created_offline',
                'status' => 'success',
                'details' => json_encode([
                    'table_number' => $order->table_number,
                    'total_amount' => $order->total_amount,
                    'items_count' => count($orderData['items'] ?? []),
                ]),
            ]);

            Log::info('Offline order created', [
                'order_id' => $order->id,
                'local_order_id' => $order->local_order_id,
                'device_id' => $order->device_id,
            ]);

            return $order;
        });
    }

    /**
     * Process a pending sync queue item
     *
     * @param int $orderId
     * @return bool
     */
    public function processSyncQueue(int $orderId): bool
    {
        $syncQueue = OrderSyncQueue::where('order_id', $orderId)
            ->where('status', 'pending')
            ->first();

        if (!$syncQueue) {
            Log::warning('No pending sync queue found for order', ['order_id' => $orderId]);
            return false;
        }

        try {
            $syncQueue->markAsProcessing();

            $order = Order::find($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Mark order as synced
            $order->update([
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            $syncQueue->markAsCompleted();

            // Log success
            DeviceSyncLog::create([
                'device_id' => $syncQueue->device_id,
                'order_id' => $order->id,
                'action' => 'order_synced',
                'status' => 'success',
                'details' => json_encode([
                    'sync_type' => $syncQueue->sync_type,
                    'retry_count' => $syncQueue->retry_count,
                ]),
            ]);

            Log::info('Order sync completed', [
                'order_id' => $orderId,
                'local_order_id' => $order->local_order_id,
            ]);

            return true;
        } catch (\Exception $e) {
            $syncQueue->markAsFailed($e->getMessage());

            DeviceSyncLog::create([
                'device_id' => $syncQueue->device_id,
                'order_id' => $orderId,
                'action' => 'order_sync_failed',
                'status' => 'failed',
                'details' => $e->getMessage(),
            ]);

            Log::error('Order sync failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get pending orders for a specific device
     *
     * @param string $deviceId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingOrdersForDevice(string $deviceId)
    {
        return Order::where('device_id', $deviceId)
            ->where('sync_status', 'pending_sync')
            ->with('items')
            ->get();
    }

    /**
     * Process batch orders from a device
     *
     * @param array $ordersData
     * @return array
     */
    public function processBatchOrders(array $ordersData): array
    {
        $results = [
            'created' => [],
            'failed' => [],
            'duplicates' => [],
        ];

        foreach ($ordersData as $orderData) {
            try {
                $order = $this->createOfflineOrder($orderData);

                if ($order === null) {
                    $results['duplicates'][] = $orderData['local_order_id'] ?? 'unknown';
                } else {
                    $results['created'][] = $order->id;
                }
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'local_order_id' => $orderData['local_order_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Validate that products have sufficient stock
     *
     * @param array $items
     * @throws \Exception
     */
    private function validateProductAvailability(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id'] ?? null);

            if (!$product) {
                throw new \Exception("Product not found: {$item['product_id']}");
            }

            $requestedQuantity = $item['quantity'] ?? 0;
            if ($product->quantity < $requestedQuantity) {
                throw new \Exception(
                    "Insufficient stock for product '{$product->name}'. " .
                    "Available: {$product->quantity}, Requested: {$requestedQuantity}"
                );
            }
        }
    }

    /**
     * Get sync statistics for a device
     *
     * @param string $deviceId
     * @return array
     */
    public function getDeviceSyncStats(string $deviceId): array
    {
        $pendingCount = Order::where('device_id', $deviceId)
            ->where('sync_status', 'pending_sync')
            ->count();

        $syncedCount = Order::where('device_id', $deviceId)
            ->where('sync_status', 'synced')
            ->count();

        $failedCount = OrderSyncQueue::where('device_id', $deviceId)
            ->where('status', 'failed')
            ->count();

        $lastSync = DeviceSyncLog::forDevice($deviceId)
            ->where('action', 'order_synced')
            ->latest()
            ->first();

        return [
            'device_id' => $deviceId,
            'pending_orders' => $pendingCount,
            'synced_orders' => $syncedCount,
            'failed_syncs' => $failedCount,
            'last_sync_at' => $lastSync?->created_at?->toIso8601String(),
        ];
    }
}
