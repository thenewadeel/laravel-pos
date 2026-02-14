<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Services\ConflictResolutionService;
use App\Services\OfflineOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    protected $offlineOrderService;
    protected $conflictResolutionService;

    public function __construct(
        OfflineOrderService $offlineOrderService,
        ConflictResolutionService $conflictResolutionService
    ) {
        $this->offlineOrderService = $offlineOrderService;
        $this->conflictResolutionService = $conflictResolutionService;
    }

    /**
     * Upload offline orders from tablet
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|min:1',
            'orders' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $deviceId = $request->device_id;
        $orders = $request->orders;
        $uploadedOrders = [];

        foreach ($orders as $orderData) {
            $orderData['device_id'] = $deviceId;
            $orderData['user_id'] = $request->user()->id;
            
            try {
                $order = $this->offlineOrderService->createOfflineOrder($orderData);
                
                $uploadedOrders[] = [
                    'local_order_id' => $orderData['local_order_id'] ?? null,
                    'server_order_id' => $order->id,
                    'status' => 'uploaded',
                ];
            } catch (\Exception $e) {
                $uploadedOrders[] = [
                    'local_order_id' => $orderData['local_order_id'] ?? null,
                    'server_order_id' => null,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uploaded_count' => count(array_filter($uploadedOrders, fn($o) => $o['status'] === 'uploaded')),
                'orders' => $uploadedOrders,
            ],
            'message' => 'Orders uploaded successfully',
        ], 201);
    }

    /**
     * Get sync status for a device
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $deviceId = $request->device_id;

        $totalOrders = OrderSyncQueue::where('device_id', $deviceId)->count();
        $pendingCount = OrderSyncQueue::where('device_id', $deviceId)->where('status', 'pending')->count();
        $completedCount = OrderSyncQueue::where('device_id', $deviceId)->where('status', 'completed')->count();
        $failedCount = OrderSyncQueue::where('device_id', $deviceId)->where('status', 'failed')->count();
        $conflictCount = OrderSyncQueue::where('device_id', $deviceId)->where('status', 'conflict')->count();

        $lastSync = OrderSyncQueue::where('device_id', $deviceId)
            ->where('status', 'completed')
            ->orderBy('processed_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $deviceId,
                'total_orders' => $totalOrders,
                'pending_count' => $pendingCount,
                'completed_count' => $completedCount,
                'failed_count' => $failedCount,
                'conflict_count' => $conflictCount,
                'last_sync_at' => $lastSync ? $lastSync->processed_at?->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Download server updates for tablet
     */
    public function download(Request $request)
    {
        $deviceId = $request->device_id;
        $lastSyncAt = $request->last_sync_at;

        $query = Order::where('device_id', $deviceId)
            ->where('sync_status', 'synced');

        if ($lastSyncAt) {
            $query->where('updated_at', '>', $lastSyncAt);
        }

        $orders = $query->with(['items.product', 'customer'])->get();

        $ordersData = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'POS_number' => $order->POS_number,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
                'type' => $order->type,
                'status' => $order->state,
                'total_amount' => $order->total_amount,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }),
                'synced_at' => $order->synced_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $ordersData,
                'products_updated' => [],
                'customers_updated' => [],
            ],
        ]);
    }

    /**
     * Acknowledge received data
     */
    public function acknowledge(Request $request)
    {
        $orderIds = $request->order_ids ?? [];
        
        // Mark orders as acknowledged
        Order::whereIn('id', $orderIds)->update([
            'synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'acknowledged_count' => count($orderIds),
            ],
            'message' => 'Data acknowledged successfully',
        ]);
    }

    /**
     * Report sync conflicts
     */
    public function reportConflict(Request $request)
    {
        $conflicts = $request->conflicts ?? [];
        $deviceId = $request->device_id;

        foreach ($conflicts as $conflictData) {
            OrderSyncQueue::create([
                'order_id' => $conflictData['server_order_id'] ?? null,
                'device_id' => $deviceId,
                'local_order_id' => $conflictData['local_order_id'] ?? 'unknown',
                'sync_type' => 'update',
                'order_data' => json_encode($conflictData),
                'status' => 'conflict',
                'conflict_data' => $conflictData['details'] ?? [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conflicts_reported' => count($conflicts),
            ],
            'message' => 'Conflicts reported successfully',
        ], 201);
    }

    /**
     * List unresolved conflicts
     */
    public function listConflicts(Request $request)
    {
        $deviceId = $request->device_id;

        $conflicts = OrderSyncQueue::where('status', 'conflict')
            ->when($deviceId, function ($query) use ($deviceId) {
                return $query->where('device_id', $deviceId);
            })
            ->get();

        $conflictsData = $conflicts->map(function ($conflict) {
            $conflictData = $conflict->conflict_data ?? [];
            return [
                'id' => $conflict->id,
                'type' => $conflictData['type'] ?? 'unknown',
                'local_order_id' => $conflict->local_order_id,
                'server_order_id' => $conflict->order_id,
                'details' => $conflictData,
                'created_at' => $conflict->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'conflicts' => $conflictsData,
                'total_count' => $conflicts->count(),
            ],
        ]);
    }

    /**
     * Resolve a conflict
     */
    public function resolveConflict(Request $request, $id)
    {
        $conflict = OrderSyncQueue::findOrFail($id);
        $resolution = $request->resolution;

        // Apply resolution strategy
        if ($resolution === 'use_server') {
            // Keep server version, discard local
            $conflict->update([
                'status' => 'completed',
                'conflict_data' => [
                    'resolution' => 'use_server',
                    'reason' => $request->reason,
                    'resolved_at' => now()->toIso8601String(),
                ],
            ]);
        } elseif ($resolution === 'update_server') {
            // Update server with local changes
            $conflict->update([
                'status' => 'completed',
                'conflict_data' => [
                    'resolution' => 'update_server',
                    'reason' => $request->reason,
                    'resolved_at' => now()->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conflict_id' => $id,
                'resolution' => $resolution,
                'status' => 'completed',
            ],
            'message' => 'Conflict resolved successfully',
        ]);
    }

    /**
     * Dismiss a conflict
     */
    public function dismissConflict($id)
    {
        $conflict = OrderSyncQueue::findOrFail($id);
        
        $conflict->update([
            'status' => 'completed',
            'conflict_data' => [
                'resolution' => 'dismissed',
                'dismissed_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'conflict_id' => $id,
                'status' => 'completed',
            ],
            'message' => 'Conflict dismissed successfully',
        ]);
    }
}