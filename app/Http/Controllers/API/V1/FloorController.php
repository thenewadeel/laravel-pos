<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FloorController extends Controller
{
    /**
     * List all floors with their tables
     */
    public function index()
    {
        $floors = Floor::with(['tables' => function ($query) {
            $query->orderBy('table_number', 'asc');
        }])
            ->active()
            ->ordered()
            ->get();

        $floorsData = $floors->map(function ($floor) {
            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'description' => $floor->description,
                'sort_order' => $floor->sort_order,
                'is_active' => $floor->is_active,
                'tables' => $floor->tables->map(function ($table) {
                    return [
                        'id' => $table->id,
                        'table_number' => $table->table_number,
                        'name' => $table->name,
                        'capacity' => $table->capacity,
                        'status' => $table->status,
                        'position_x' => $table->position_x,
                        'position_y' => $table->position_y,
                        'width' => $table->width,
                        'height' => $table->height,
                        'shape' => $table->shape,
                        'is_active' => $table->is_active,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $floorsData,
        ]);
    }

    /**
     * Create a new floor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $floor = Floor::create([
            'shop_id' => $request->user()->shop_id ?? 1,
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'description' => $floor->description,
                'sort_order' => $floor->sort_order,
                'is_active' => $floor->is_active,
                'tables' => [],
            ],
            'message' => 'Floor created successfully',
        ], 201);
    }

    /**
     * Update a floor
     */
    public function update(Request $request, $id)
    {
        $floor = Floor::find($id);

        if (!$floor) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Floor not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $floor->update($request->only(['name', 'description', 'sort_order', 'is_active']));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'description' => $floor->description,
                'sort_order' => $floor->sort_order,
                'is_active' => $floor->is_active,
            ],
            'message' => 'Floor updated successfully',
        ]);
    }

    /**
     * Delete a floor
     */
    public function destroy($id)
    {
        $floor = Floor::find($id);

        if (!$floor) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Floor not found',
                ],
            ], 404);
        }

        // Check if floor has active tables with orders
        $hasActiveTables = $floor->tables()->where('status', 'occupied')->exists();
        
        if ($hasActiveTables) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONFLICT',
                    'message' => 'Cannot delete floor with active orders',
                ],
            ], 409);
        }

        // Soft delete the floor and its tables
        $floor->tables()->delete();
        $floor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Floor deleted successfully',
        ]);
    }

    /**
     * Create a new table for a floor
     */
    public function storeTable(Request $request, $floorId)
    {
        $floor = Floor::find($floorId);

        if (!$floor) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Floor not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:50',
            'name' => 'nullable|string|max:100',
            'capacity' => 'required|integer|min:1',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shape' => 'nullable|in:rectangle,circle,square',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Check if table number already exists on this floor
        $exists = $floor->tables()->where('table_number', $request->table_number)->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE',
                    'message' => 'Table number already exists on this floor',
                ],
            ], 422);
        }

        $table = $floor->tables()->create([
            'table_number' => $request->table_number,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'status' => 'available',
            'position_x' => $request->position_x ?? 0,
            'position_y' => $request->position_y ?? 0,
            'width' => $request->width ?? 100,
            'height' => $request->height ?? 100,
            'shape' => $request->shape ?? 'rectangle',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'position_x' => $table->position_x,
                'position_y' => $table->position_y,
                'width' => $table->width,
                'height' => $table->height,
                'shape' => $table->shape,
                'is_active' => $table->is_active,
            ],
            'message' => 'Table created successfully',
        ], 201);
    }

    /**
     * Update a table
     */
    public function updateTable(Request $request, $id)
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Table not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'table_number' => 'sometimes|string|max:50',
            'name' => 'nullable|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:available,occupied,reserved,cleaning',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shape' => 'nullable|in:rectangle,circle,square',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Check for duplicate table number if changing
        if ($request->has('table_number') && $request->table_number !== $table->table_number) {
            $exists = RestaurantTable::where('floor_id', $table->floor_id)
                ->where('table_number', $request->table_number)
                ->where('id', '!=', $id)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DUPLICATE',
                        'message' => 'Table number already exists on this floor',
                    ],
                ], 422);
            }
        }

        $table->update($request->only([
            'table_number', 'name', 'capacity', 'status', 
            'position_x', 'position_y', 'width', 'height', 
            'shape', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'position_x' => $table->position_x,
                'position_y' => $table->position_y,
                'width' => $table->width,
                'height' => $table->height,
                'shape' => $table->shape,
                'is_active' => $table->is_active,
            ],
            'message' => 'Table updated successfully',
        ]);
    }

    /**
     * Delete a table
     */
    public function destroyTable($id)
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Table not found',
                ],
            ], 404);
        }

        // Check if table has active orders
        if ($table->status === 'occupied') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CONFLICT',
                    'message' => 'Cannot delete table with active orders',
                ],
            ], 409);
        }

        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Table deleted successfully',
        ]);
    }

    /**
     * Update table status
     */
    public function updateTableStatus(Request $request, $id)
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Table not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,occupied,reserved,cleaning',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $table->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $table->id,
                'status' => $table->status,
            ],
            'message' => 'Table status updated successfully',
        ]);
    }
}
