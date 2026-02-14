<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOfflineSyncQueue;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Services\OfflineOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOfflineOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-offline 
                            {--device= : Sync orders for specific device}
                            {--order= : Sync specific order by ID}
                            {--all : Sync all pending orders}
                            {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync offline orders from tablets to the server';

    /**
     * Execute the console command.
     */
    public function handle(OfflineOrderService $orderService)
    {
        $deviceId = $this->option('device');
        $orderId = $this->option('order');
        $syncAll = $this->option('all');
        $dryRun = $this->option('dry-run');

        // Determine what to sync
        if ($orderId) {
            return $this->syncSingleOrder($orderId, $orderService, $dryRun);
        } elseif ($deviceId) {
            return $this->syncDeviceOrders($deviceId, $orderService, $dryRun);
        } elseif ($syncAll) {
            return $this->syncAllPendingOrders($orderService, $dryRun);
        } else {
            $this->showSyncStatus();
            return 0;
        }
    }

    /**
     * Sync a single order
     */
    private function syncSingleOrder(int $orderId, OfflineOrderService $orderService, bool $dryRun): int
    {
        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Order not found: {$orderId}");
            return 1;
        }

        if ($order->sync_status !== 'pending_sync') {
            $this->warn("Order {$orderId} is not pending sync (status: {$order->sync_status})");
            return 0;
        }

        $this->info("Order {$orderId}:");
        $this->info("  Local ID: {$order->local_order_id}");
        $this->info("  Device: {$order->device_id}");
        $this->info("  Table: {$order->table_number}");
        $this->info("  Amount: {$order->total_amount}");

        if ($dryRun) {
            $this->info("  [DRY RUN] Would sync this order");
            return 0;
        }

        try {
            $success = $orderService->processSyncQueue($orderId);

            if ($success) {
                $this->info("  ✓ Synced successfully");
                Log::info('Manual sync completed', ['order_id' => $orderId]);
                return 0;
            } else {
                $this->error("  ✗ Failed to sync");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Error: {$e->getMessage()}");
            Log::error('Manual sync failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Sync all pending orders for a device
     */
    private function syncDeviceOrders(string $deviceId, OfflineOrderService $orderService, bool $dryRun): int
    {
        $pendingOrders = Order::where('device_id', $deviceId)
            ->where('sync_status', 'pending_sync')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->warn("No pending orders found for device: {$deviceId}");
            return 0;
        }

        $this->info("Found {$pendingOrders->count()} pending orders for device: {$deviceId}");

        if ($dryRun) {
            foreach ($pendingOrders as $order) {
                $this->info("  [DRY RUN] Would sync order {$order->id} ({$order->local_order_id})");
            }
            return 0;
        }

        // Dispatch job for async processing
        ProcessOfflineSyncQueue::dispatch(null, $deviceId);

        $this->info("✓ Dispatched sync job for device: {$deviceId}");
        $this->info("  The orders will be processed by the queue worker.");

        return 0;
    }

    /**
     * Sync all pending orders system-wide
     */
    private function syncAllPendingOrders(OfflineOrderService $orderService, bool $dryRun): int
    {
        $pendingOrders = Order::where('sync_status', 'pending_sync')->get();

        if ($pendingOrders->isEmpty()) {
            $this->warn("No pending orders found system-wide");
            return 0;
        }

        $this->info("Found {$pendingOrders->count()} pending orders system-wide");

        // Group by device for better reporting
        $byDevice = $pendingOrders->groupBy('device_id');
        foreach ($byDevice as $deviceId => $orders) {
            $this->info("  Device {$deviceId}: {$orders->count()} orders");
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Would dispatch sync jobs for all devices");
            return 0;
        }

        // Dispatch job for all pending orders
        ProcessOfflineSyncQueue::dispatch(null, null, true);

        $this->info("✓ Dispatched sync job for all pending orders");
        $this->info("  The orders will be processed by the queue worker.");

        return 0;
    }

    /**
     * Show current sync status
     */
    private function showSyncStatus(): void
    {
        $this->info("Offline Order Sync Status");
        $this->info("=========================");

        $total = Order::whereNotNull('device_id')->count();
        $pending = Order::where('sync_status', 'pending_sync')->count();
        $synced = Order::where('sync_status', 'synced')->count();
        $failed = OrderSyncQueue::where('status', 'failed')->count();
        $conflicts = OrderSyncQueue::where('status', 'conflict')->count();

        $this->info("Total Orders: {$total}");
        $this->info("Pending: {$pending}");
        $this->info("Synced: {$synced}");
        $this->info("Failed: {$failed}");
        $this->info("Conflicts: {$conflicts}");

        $this->newLine();
        $this->info("Usage:");
        $this->info("  php artisan orders:sync-offline --all          # Sync all pending orders");
        $this->info("  php artisan orders:sync-offline --device=ID    # Sync specific device");
        $this->info("  php artisan orders:sync-offline --order=ID     # Sync specific order");
        $this->info("  php artisan orders:sync-offline --dry-run      # Preview without syncing");
    }
}
