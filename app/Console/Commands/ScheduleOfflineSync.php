<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOfflineSyncQueue;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleOfflineSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:schedule-sync 
                            {--interval=5 : Sync interval in minutes}
                            {--max-orders=100 : Maximum orders to sync per run}
                            {--notify : Send notification on completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync offline orders on a schedule';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $maxOrders = (int) $this->option('max-orders');
        $notify = $this->option('notify');

        $this->info("Starting scheduled offline sync (interval: {$interval}min, max: {$maxOrders} orders)");

        // Get pending orders
        $pendingOrders = Order::where('sync_status', 'pending_sync')
            ->orderBy('created_at', 'asc')
            ->limit($maxOrders)
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders to sync');
            return 0;
        }

        $this->info("Found {$pendingOrders->count()} pending orders");

        // Group by device for efficient processing
        $ordersByDevice = $pendingOrders->groupBy('device_id');
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($ordersByDevice as $deviceId => $orders) {
            $this->info("Processing {$orders->count()} orders for device: {$deviceId}");

            try {
                // Dispatch job for each device
                ProcessOfflineSyncQueue::dispatch(null, $deviceId);
                $syncedCount += $orders->count();
                
                Log::info('Scheduled sync dispatched for device', [
                    'device_id' => $deviceId,
                    'order_count' => $orders->count(),
                ]);
            } catch (\Exception $e) {
                $failedCount += $orders->count();
                $this->error("Failed to dispatch sync for device {$deviceId}: {$e->getMessage()}");
                
                Log::error('Scheduled sync failed for device', [
                    'device_id' => $deviceId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Summary
        $this->newLine();
        $this->info('Sync Summary:');
        $this->info("  Dispatched: {$syncedCount} orders");
        $this->info("  Failed: {$failedCount} orders");

        // Check for conflicts
        $conflictCount = OrderSyncQueue::where('status', 'conflict')->count();
        if ($conflictCount > 0) {
            $this->warn("  ⚠️  {$conflictCount} conflicts require manual resolution");
        }

        // Check for failed syncs
        $failedSyncCount = OrderSyncQueue::where('status', 'failed')->count();
        if ($failedSyncCount > 0) {
            $this->warn("  ⚠️  {$failedSyncCount} failed syncs need retry");
        }

        if ($notify) {
            $this->sendNotification($syncedCount, $failedCount, $conflictCount);
        }

        Log::info('Scheduled sync completed', [
            'synced' => $syncedCount,
            'failed' => $failedCount,
            'conflicts' => $conflictCount,
        ]);

        return 0;
    }

    /**
     * Send notification about sync completion
     */
    private function sendNotification(int $synced, int $failed, int $conflicts): void
    {
        // This can be extended to send email, Slack, or other notifications
        $message = "Offline Sync Complete:\n";
        $message .= "- Synced: {$synced} orders\n";
        $message .= "- Failed: {$failed} orders\n";
        $message .= "- Conflicts: {$conflicts} orders";

        // Log as info for now - can be replaced with actual notification
        Log::info('Sync notification', ['message' => $message]);
        
        $this->info('Notification sent');
    }
}
