<?php

namespace App\Jobs;

use App\Models\DeviceSyncLog;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Services\ConflictResolutionService;
use App\Services\OfflineOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOfflineSyncQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 300, 600]; // Exponential backoff in seconds

    protected ?int $orderId;
    protected ?string $deviceId;
    protected bool $processAllPending;

    /**
     * Create a new job instance.
     *
     * @param int|null $orderId Specific order to process
     * @param string|null $deviceId Process all pending for device
     * @param bool $processAllPending Process all pending orders
     */
    public function __construct(?int $orderId = null, ?string $deviceId = null, bool $processAllPending = false)
    {
        $this->orderId = $orderId;
        $this->deviceId = $deviceId;
        $this->processAllPending = $processAllPending;
    }

    /**
     * Execute the job.
     */
    public function handle(
        OfflineOrderService $orderService,
        ConflictResolutionService $conflictService
    ): void {
        if ($this->orderId) {
            $this->processSingleOrder($this->orderId, $orderService, $conflictService);
        } elseif ($this->deviceId) {
            $this->processDeviceOrders($this->deviceId, $orderService, $conflictService);
        } elseif ($this->processAllPending) {
            $this->processAllPendingOrders($orderService, $conflictService);
        }
    }

    /**
     * Process a single order
     */
    private function processSingleOrder(
        int $orderId,
        OfflineOrderService $orderService,
        ConflictResolutionService $conflictService
    ): void {
        $syncQueue = OrderSyncQueue::where('order_id', $orderId)
            ->where('status', 'pending')
            ->first();

        if (!$syncQueue) {
            Log::info('No pending sync queue found for order', ['order_id' => $orderId]);
            return;
        }

        $this->processSyncItem($syncQueue, $orderService, $conflictService);
    }

    /**
     * Process all pending orders for a device
     */
    private function processDeviceOrders(
        string $deviceId,
        OfflineOrderService $orderService,
        ConflictResolutionService $conflictService
    ): void {
        $pendingQueues = OrderSyncQueue::forDevice($deviceId)
            ->pending()
            ->with('order')
            ->get();

        Log::info('Processing device sync queue', [
            'device_id' => $deviceId,
            'pending_count' => $pendingQueues->count(),
        ]);

        foreach ($pendingQueues as $syncQueue) {
            $this->processSyncItem($syncQueue, $orderService, $conflictService);
        }
    }

    /**
     * Process all pending orders system-wide
     */
    private function processAllPendingOrders(
        OfflineOrderService $orderService,
        ConflictResolutionService $conflictService
    ): void {
        $pendingQueues = OrderSyncQueue::pending()
            ->with('order')
            ->limit(100) // Process in batches
            ->get();

        Log::info('Processing all pending sync queues', [
            'pending_count' => $pendingQueues->count(),
        ]);

        foreach ($pendingQueues as $syncQueue) {
            $this->processSyncItem($syncQueue, $orderService, $conflictService);
        }
    }

    /**
     * Process a single sync queue item
     */
    private function processSyncItem(
        OrderSyncQueue $syncQueue,
        OfflineOrderService $orderService,
        ConflictResolutionService $conflictService
    ): void {
        try {
            $syncQueue->markAsProcessing();

            $order = $syncQueue->order;
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Check for conflicts
            $orderData = [
                'shop_id' => $order->shop_id,
                'user_id' => $order->user_id,
                'customer_id' => $order->customer_id,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
                'type' => $order->type,
                'items' => $order->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ])->toArray(),
                'subtotal' => $order->subtotal,
                'total_amount' => $order->total_amount,
                'device_id' => $order->device_id,
                'local_order_id' => $order->local_order_id,
            ];

            $conflict = $conflictService->detectConflict($orderData, $order->id);

            if ($conflict) {
                // Handle conflict
                $this->handleConflict($syncQueue, $order, $orderData, $conflict, $conflictService);
            } else {
                // No conflict, proceed with sync
                $success = $orderService->processSyncQueue($order->id);

                if ($success) {
                    Log::info('Order synced successfully', [
                        'order_id' => $order->id,
                        'local_order_id' => $order->local_order_id,
                    ]);
                } else {
                    throw new \Exception('Failed to process sync queue');
                }
            }
        } catch (\Exception $e) {
            $this->handleFailure($syncQueue, $e);
        }
    }

    /**
     * Handle conflict resolution
     */
    private function handleConflict(
        OrderSyncQueue $syncQueue,
        Order $order,
        array $orderData,
        array $conflict,
        ConflictResolutionService $conflictService
    ): void {
        Log::warning('Conflict detected during sync', [
            'order_id' => $order->id,
            'conflict_type' => $conflict['type'],
        ]);

        switch ($conflict['type']) {
            case 'duplicate_order':
                // Try auto-resolution for identical orders
                $existingOrder = Order::find($conflict['existing_order_id']);
                if ($existingOrder) {
                    $autoResolve = $conflictService->autoResolveIfIdentical($orderData, $existingOrder);
                    
                    if ($autoResolve['auto_resolved']) {
                        // Delete the duplicate order
                        $order->delete();
                        $syncQueue->markAsCompleted();
                        
                        DeviceSyncLog::create([
                            'device_id' => $syncQueue->device_id,
                            'order_id' => $existingOrder->id,
                            'action' => 'duplicate_auto_resolved',
                            'status' => 'success',
                            'details' => json_encode(['reason' => 'identical_order']),
                        ]);
                        
                        return;
                    } else {
                        // Mark as conflict for manual resolution
                        $syncQueue->markAsConflict($conflict);
                    }
                }
                break;

            case 'insufficient_inventory':
                // Auto-adjust if possible
                if ($conflict['available_quantity'] > 0) {
                    $result = $conflictService->resolveInventoryConflict(
                        $order,
                        $orderData['items'],
                        'adjust_quantity'
                    );
                    
                    if ($result['success']) {
                        $syncQueue->markAsCompleted();
                        
                        DeviceSyncLog::create([
                            'device_id' => $syncQueue->device_id,
                            'order_id' => $order->id,
                            'action' => 'inventory_auto_adjusted',
                            'status' => 'success',
                            'details' => json_encode($result['adjusted_items']),
                        ]);
                        
                        return;
                    }
                }
                
                // Mark as conflict for manual resolution
                $syncQueue->markAsConflict($conflict);
                break;

            default:
                $syncQueue->markAsConflict($conflict);
        }
    }

    /**
     * Handle sync failure
     */
    private function handleFailure(OrderSyncQueue $syncQueue, \Exception $e): void
    {
        $errorMessage = $e->getMessage();
        
        Log::error('Order sync failed', [
            'order_id' => $syncQueue->order_id,
            'error' => $errorMessage,
            'attempt' => $syncQueue->retry_count + 1,
        ]);

        // Check if we should retry
        if ($syncQueue->retry_count < 4) { // Max 5 attempts (0-4)
            $syncQueue->markAsFailed($errorMessage);
            
            // Re-dispatch with delay
            $delay = $this->calculateRetryDelay($syncQueue->retry_count);
            self::dispatch($syncQueue->order_id)
                ->delay(now()->addSeconds($delay));
        } else {
            // Max retries reached, mark as permanently failed
            $syncQueue->markAsFailed($errorMessage);
            
            DeviceSyncLog::create([
                'device_id' => $syncQueue->device_id,
                'order_id' => $syncQueue->order_id,
                'action' => 'sync_permanently_failed',
                'status' => 'failed',
                'details' => $errorMessage,
            ]);
        }
    }

    /**
     * Calculate retry delay with exponential backoff
     */
    private function calculateRetryDelay(int $retryCount): int
    {
        $delays = [10, 30, 60, 300, 600]; // 10s, 30s, 1m, 5m, 10m
        return $delays[$retryCount] ?? 600;
    }

    /**
     * Handle job failure (after all retries exhausted)
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessOfflineSyncQueue job failed permanently', [
            'order_id' => $this->orderId,
            'device_id' => $this->deviceId,
            'error' => $exception->getMessage(),
        ]);
    }
}
