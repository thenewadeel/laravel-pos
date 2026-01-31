<?php

namespace App\Services;

use App\Models\RestaurantTable;
use App\Models\TableOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FloorSyncService
{
    /**
     * Process table assignments from offline device
     */
    public function processAssignments(array $assignments, string $deviceId): array
    {
        $processed = 0;
        $failed = 0;
        $conflicts = [];

        foreach ($assignments as $assignment) {
            try {
                $table = RestaurantTable::find($assignment['table_id']);
                
                if (!$table) {
                    $conflicts[] = [
                        'table_id' => $assignment['table_id'],
                        'reason' => 'table_not_found',
                    ];
                    $failed++;
                    continue;
                }

                // Check if table is already occupied
                if ($table->isOccupied()) {
                    $conflicts[] = [
                        'table_id' => $table->id,
                        'reason' => 'table_already_occupied',
                    ];
                    $failed++;
                    continue;
                }

                // Assign order to table
                DB::transaction(function () use ($table, $assignment) {
                    $table->assignOrder($assignment['order_id']);
                });

                $processed++;
                
                Log::info('Table assignment synced', [
                    'device_id' => $deviceId,
                    'table_id' => $table->id,
                    'order_id' => $assignment['order_id'],
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to sync table assignment', [
                    'device_id' => $deviceId,
                    'assignment' => $assignment,
                    'error' => $e->getMessage(),
                ]);
                
                $conflicts[] = [
                    'table_id' => $assignment['table_id'],
                    'reason' => 'sync_error',
                    'message' => $e->getMessage(),
                ];
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Get sync status for device
     */
    public function getDeviceSyncStatus(string $deviceId): array
    {
        // In a real implementation, this would track sync state in a database
        // For now, return basic status
        return [
            'device_id' => $deviceId,
            'last_sync' => now()->toIso8601String(),
            'pending_uploads' => 0,
            'pending_downloads' => 0,
            'sync_status' => 'synced',
        ];
    }

    /**
     * Get updates since last sync timestamp
     */
    public function getUpdatesSince(int $shopId, string $since): array
    {
        $sinceTime = Carbon::parse($since);

        // Get new table assignments since last sync
        $assignments = DB::table('table_orders')
            ->join('restaurant_tables', 'table_orders.table_id', '=', 'restaurant_tables.id')
            ->join('floors', 'restaurant_tables.floor_id', '=', 'floors.id')
            ->where('floors.shop_id', $shopId)
            ->where('table_orders.created_at', '>', $sinceTime)
            ->select(
                'table_orders.table_id',
                'table_orders.order_id',
                'table_orders.started_at as assigned_at',
                'table_orders.is_active as status',
                'restaurant_tables.status as table_status'
            )
            ->get();

        // Get table status updates since last sync
        $tableUpdates = RestaurantTable::whereHas('floor', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->where('updated_at', '>', $sinceTime)
            ->select('id', 'status', 'updated_at')
            ->get();

        return [
            'assignments' => $assignments,
            'table_updates' => $tableUpdates,
            'sync_timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Acknowledge sync data received by device
     */
    public function acknowledgeSync(string $deviceId, string $syncTimestamp, array $receivedItems): void
    {
        Log::info('Sync acknowledged', [
            'device_id' => $deviceId,
            'sync_timestamp' => $syncTimestamp,
            'received_items_count' => count($receivedItems),
        ]);

        // In a real implementation, this would update sync tracking records
        // to mark items as received by the device
    }
}
