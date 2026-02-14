<?php

namespace App\Services;

use App\Models\DeviceSyncLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConflictResolutionService
{
    /**
     * Detect conflicts for an incoming offline order
     *
     * @param array $orderData
     * @param int|null $excludeOrderId Order ID to exclude from duplicate check (for syncing existing orders)
     * @return array|null
     */
    public function detectConflict(array $orderData, ?int $excludeOrderId = null): ?array
    {
        // Check for duplicate by local_order_id
        if (isset($orderData['local_order_id'])) {
            $query = Order::where('local_order_id', $orderData['local_order_id']);
            
            if ($excludeOrderId) {
                $query->where('id', '!=', $excludeOrderId);
            }
            
            $existingOrder = $query->first();
            
            if ($existingOrder) {
                return [
                    'type' => 'duplicate_order',
                    'existing_order_id' => $existingOrder->id,
                    'local_order_id' => $orderData['local_order_id'],
                    'message' => 'Order with this local ID already exists',
                ];
            }
        }

        // Check for inventory conflicts
        $inventoryConflict = $this->detectInventoryConflict($orderData);
        if ($inventoryConflict) {
            return $inventoryConflict;
        }

        return null;
    }

    /**
     * Detect inventory conflicts
     *
     * @param array $orderData
     * @return array|null
     */
    public function detectInventoryConflict(array $orderData): ?array
    {
        foreach ($orderData['items'] ?? [] as $item) {
            $product = Product::find($item['product_id'] ?? null);
            
            if (!$product) {
                continue;
            }

            $requestedQuantity = $item['quantity'] ?? 0;
            
            if ($product->quantity < $requestedQuantity) {
                return [
                    'type' => 'insufficient_inventory',
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'available_quantity' => $product->quantity,
                    'requested_quantity' => $requestedQuantity,
                    'message' => "Insufficient inventory for product '{$product->name}'",
                ];
            }
        }

        return null;
    }

    /**
     * Resolve a conflict using specified strategy
     *
     * @param array $orderData
     * @param Order $existingOrder
     * @param string $strategy
     * @return array
     */
    public function resolveConflict(array $orderData, Order $existingOrder, string $strategy): array
    {
        switch ($strategy) {
            case 'use_server':
                return $this->resolveUseServer($existingOrder);
                
            case 'update_server':
                return $this->resolveUpdateServer($orderData, $existingOrder);
                
            case 'merge':
                return $this->mergeOrders($existingOrder, $orderData);
                
            default:
                return [
                    'success' => false,
                    'error' => 'Unknown resolution strategy',
                ];
        }
    }

    /**
     * Resolve by using server version (discard local changes)
     *
     * @param Order $existingOrder
     * @return array
     */
    private function resolveUseServer(Order $existingOrder): array
    {
        // Mark as synced and log
        $existingOrder->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);

        DeviceSyncLog::create([
            'device_id' => $existingOrder->device_id ?? 'unknown',
            'order_id' => $existingOrder->id,
            'action' => 'conflict_resolved_use_server',
            'status' => 'success',
            'details' => json_encode(['resolution' => 'server_version_used']),
        ]);

        Log::info('Conflict resolved: Using server version', [
            'order_id' => $existingOrder->id,
            'local_order_id' => $existingOrder->local_order_id,
        ]);

        return [
            'success' => true,
            'order_id' => $existingOrder->id,
            'resolution' => 'server_version_used',
        ];
    }

    /**
     * Resolve by updating server version with local data
     *
     * @param array $orderData
     * @param Order $existingOrder
     * @return array
     */
    private function resolveUpdateServer(array $orderData, Order $existingOrder): array
    {
        return DB::transaction(function () use ($orderData, $existingOrder) {
            // Restore original product quantities
            foreach ($existingOrder->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                }
            }

            // Delete old items
            $existingOrder->items()->delete();

            // Create new items
            foreach ($orderData['items'] ?? [] as $itemData) {
                OrderItem::create([
                    'order_id' => $existingOrder->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['unit_price'] ?? 0,
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'total_price' => $itemData['total_price'] ?? 0,
                ]);

                // Decrement new quantities
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->decrement('quantity', $itemData['quantity']);
                }
            }

            // Update order totals
            $existingOrder->update([
                'table_number' => $orderData['table_number'] ?? $existingOrder->table_number,
                'waiter_name' => $orderData['waiter_name'] ?? $existingOrder->waiter_name,
                'type' => $orderData['type'] ?? $existingOrder->type,
                'subtotal' => $orderData['subtotal'] ?? $existingOrder->subtotal,
                'total_amount' => $orderData['total_amount'] ?? $existingOrder->total_amount,
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            DeviceSyncLog::create([
                'device_id' => $existingOrder->device_id ?? 'unknown',
                'order_id' => $existingOrder->id,
                'action' => 'conflict_resolved_update_server',
                'status' => 'success',
                'details' => json_encode(['resolution' => 'server_version_updated']),
            ]);

            Log::info('Conflict resolved: Server version updated', [
                'order_id' => $existingOrder->id,
                'local_order_id' => $existingOrder->local_order_id,
            ]);

            return [
                'success' => true,
                'order_id' => $existingOrder->id,
                'resolution' => 'server_version_updated',
            ];
        });
    }

    /**
     * Merge orders (add items from local to server version)
     *
     * @param Order $existingOrder
     * @param array $orderData
     * @return array
     */
    public function mergeOrders(Order $existingOrder, array $orderData): array
    {
        return DB::transaction(function () use ($orderData, $existingOrder) {
            $existingItemIds = $existingOrder->items->pluck('product_id')->toArray();
            $newTotal = $existingOrder->total_amount;

            foreach ($orderData['items'] ?? [] as $itemData) {
                // If item already exists, update quantity
                if (in_array($itemData['product_id'], $existingItemIds)) {
                    $existingItem = $existingOrder->items()
                        ->where('product_id', $itemData['product_id'])
                        ->first();
                    
                    if ($existingItem) {
                        // Restore original quantity first
                        $product = Product::find($itemData['product_id']);
                        if ($product) {
                            $product->increment('quantity', $existingItem->quantity);
                        }

                        // Update with new quantity
                        $existingItem->update([
                            'quantity' => $itemData['quantity'],
                            'total_price' => $itemData['total_price'],
                        ]);

                        // Decrement new quantity
                        if ($product) {
                            $product->decrement('quantity', $itemData['quantity']);
                        }
                    }
                } else {
                    // Add new item
                    OrderItem::create([
                        'order_id' => $existingOrder->id,
                        'product_id' => $itemData['product_id'],
                        'product_name' => $itemData['product_name'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['unit_price'] ?? 0,
                        'unit_price' => $itemData['unit_price'] ?? 0,
                        'total_price' => $itemData['total_price'] ?? 0,
                    ]);

                    // Decrement stock
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        $product->decrement('quantity', $itemData['quantity']);
                    }
                }

                $newTotal = $orderData['total_amount'];
            }

            $existingOrder->update([
                'total_amount' => $newTotal,
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            DeviceSyncLog::create([
                'device_id' => $existingOrder->device_id ?? 'unknown',
                'order_id' => $existingOrder->id,
                'action' => 'conflict_resolved_merge',
                'status' => 'success',
                'details' => json_encode(['resolution' => 'orders_merged']),
            ]);

            Log::info('Conflict resolved: Orders merged', [
                'order_id' => $existingOrder->id,
                'local_order_id' => $existingOrder->local_order_id,
            ]);

            return [
                'success' => true,
                'order_id' => $existingOrder->id,
                'resolution' => 'orders_merged',
            ];
        });
    }

    /**
     * Resolve inventory conflict
     *
     * @param Order $order
     * @param array $items
     * @param string $strategy
     * @return array
     */
    public function resolveInventoryConflict(Order $order, array $items, string $strategy): array
    {
        if ($strategy === 'adjust_quantity') {
            return DB::transaction(function () use ($order, $items) {
                $adjustedItems = [];
                $newTotal = 0;

                foreach ($items as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    $requestedQuantity = $itemData['quantity'];
                    $adjustedQuantity = min($requestedQuantity, $product->quantity);
                    
                    $orderItem = $order->items()->where('product_id', $itemData['product_id'])->first();
                    if ($orderItem) {
                        // Restore original quantity
                        $product->increment('quantity', $orderItem->quantity);
                        
                        // Update with adjusted quantity
                        $orderItem->update([
                            'quantity' => $adjustedQuantity,
                            'total_price' => $adjustedQuantity * $itemData['unit_price'],
                        ]);
                        
                        // Decrement adjusted quantity
                        $product->decrement('quantity', $adjustedQuantity);
                    }

                    $adjustedItems[] = [
                        'product_id' => $itemData['product_id'],
                        'requested' => $requestedQuantity,
                        'adjusted' => $adjustedQuantity,
                    ];

                    $newTotal += $adjustedQuantity * $itemData['unit_price'];
                }

                $order->update([
                    'total_amount' => $newTotal,
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);

                return [
                    'success' => true,
                    'adjusted_items' => $adjustedItems,
                    'new_total' => $newTotal,
                ];
            });
        }

        return [
            'success' => false,
            'error' => 'Unknown inventory resolution strategy',
        ];
    }

    /**
     * Auto-resolve if orders are identical
     *
     * @param array $orderData
     * @param Order $existingOrder
     * @return array
     */
    public function autoResolveIfIdentical(array $orderData, Order $existingOrder): array
    {
        // Check if orders are identical
        $isIdentical = $this->areOrdersIdentical($orderData, $existingOrder);

        if ($isIdentical) {
            // Mark as synced
            $existingOrder->update([
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            DeviceSyncLog::create([
                'device_id' => $existingOrder->device_id ?? 'unknown',
                'order_id' => $existingOrder->id,
                'action' => 'conflict_auto_resolved_identical',
                'status' => 'success',
                'details' => json_encode(['reason' => 'identical_order']),
            ]);

            return [
                'auto_resolved' => true,
                'reason' => 'identical_order',
                'order_id' => $existingOrder->id,
            ];
        }

        return [
            'auto_resolved' => false,
            'reason' => 'orders_differ',
        ];
    }

    /**
     * Check if two orders are identical
     *
     * @param array $orderData
     * @param Order $existingOrder
     * @return bool
     */
    private function areOrdersIdentical(array $orderData, Order $existingOrder): bool
    {
        // Compare basic fields
        if ($orderData['table_number'] !== $existingOrder->table_number) {
            return false;
        }

        if ($orderData['total_amount'] != $existingOrder->total_amount) {
            return false;
        }

        if (count($orderData['items'] ?? []) !== $existingOrder->items()->count()) {
            return false;
        }

        // Compare items
        foreach ($orderData['items'] ?? [] as $itemData) {
            $existingItem = $existingOrder->items()
                ->where('product_id', $itemData['product_id'])
                ->first();

            if (!$existingItem) {
                return false;
            }

            if ($existingItem->quantity != $itemData['quantity']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get conflict summary for a device
     *
     * @param string $deviceId
     * @return array
     */
    public function getConflictSummary(string $deviceId): array
    {
        $totalOrders = Order::where('device_id', $deviceId)->count();
        
        $pendingSyncs = OrderSyncQueue::forDevice($deviceId)
            ->pending()
            ->count();
        
        $conflicts = OrderSyncQueue::forDevice($deviceId)
            ->where('status', 'conflict')
            ->count();
        
        $failed = OrderSyncQueue::forDevice($deviceId)
            ->failed()
            ->count();

        return [
            'device_id' => $deviceId,
            'total_orders' => $totalOrders,
            'pending_syncs' => $pendingSyncs,
            'conflicts_detected' => $conflicts,
            'failed_syncs' => $failed,
            'resolvable_automatically' => $conflicts, // Simplified
            'requires_manual_intervention' => 0,
        ];
    }
}
