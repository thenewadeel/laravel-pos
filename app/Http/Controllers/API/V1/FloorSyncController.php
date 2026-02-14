<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\Shop;
use App\Services\FloorSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FloorSyncController extends Controller
{
    protected FloorSyncService $syncService;

    public function __construct(FloorSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Download floor data for offline sync
     */
    public function downloadFloors(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $shopId = $request->input('shop_id');
        
        $floors = Floor::with(['tables' => function ($query) {
                $query->where('is_active', true)
                      ->select('id', 'floor_id', 'table_number', 'name', 'capacity', 
                               'status', 'position_x', 'position_y', 'width', 'height', 'shape');
            }])
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'floors' => $floors,
                'sync_timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Upload table assignments from offline tablet
     */
    public function uploadAssignments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'device_id' => 'required|string',
            'assignments' => 'required|array',
            'assignments.*.table_id' => 'required|exists:restaurant_tables,id',
            'assignments.*.order_id' => 'required|exists:orders,id',
            'assignments.*.assigned_at' => 'required|date',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->syncService->processAssignments(
            $request->input('assignments'),
            $request->input('device_id')
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get sync status for device
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $deviceId = $request->input('device_id');
        $status = $this->syncService->getDeviceSyncStatus($deviceId);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Download server updates since last sync
     */
    public function downloadUpdates(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'since' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $shopId = $request->input('shop_id');
        $since = $request->input('since');

        $updates = $this->syncService->getUpdatesSince($shopId, $since);

        return response()->json([
            'success' => true,
            'data' => $updates,
        ]);
    }

    /**
     * Acknowledge received sync data
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'sync_timestamp' => 'required|date',
            'received_items' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $this->syncService->acknowledgeSync(
            $request->input('device_id'),
            $request->input('sync_timestamp'),
            $request->input('received_items', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Sync acknowledged',
        ]);
    }
}
