<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * List all orders with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        
        $orders = Order::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $ordersData = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'POS_number' => $order->POS_number,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
                'type' => $order->type,
                'status' => $order->state,
                'total_amount' => $order->total_amount,
                'customer' => $order->customer ? [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                ] : null,
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
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $ordersData,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get a specific order
     */
    public function show($id)
    {
        $order = Order::with(['customer', 'items.product'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'POS_number' => $order->POS_number,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
                'type' => $order->type,
                'status' => $order->state,
                'total_amount' => $order->total_amount,
                'customer' => $order->customer ? [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                ] : null,
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
                'created_at' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'table_number' => 'required|string|max:50',
            'waiter_name' => 'required|string|max:100',
            'type' => 'required|in:dine-in,take-away,delivery',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
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

        // Calculate total and create order
        $totalAmount = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $totalAmount += $itemTotal;
            
            $itemsData[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $itemTotal,
            ];
        }

        $order = Order::create([
            'POS_number' => 'POS-' . time() . rand(1000, 9999),
            'customer_id' => $request->customer_id,
            'table_number' => $request->table_number,
            'waiter_name' => $request->waiter_name,
            'type' => $request->type,
            'state' => 'preparing',
            'total_amount' => $totalAmount,
            'user_id' => auth()->id(),
        ]);

        // Create order items
        foreach ($itemsData as $itemData) {
            $itemData['price'] = $itemData['unit_price']; // Required field
            $itemData['product_name'] = Product::find($itemData['product_id'])->name;
            $itemData['product_rate'] = $itemData['unit_price'];
            $order->items()->create($itemData);
            
            // Decrement product quantity
            $product = Product::find($itemData['product_id']);
            $product->decrement('quantity', $itemData['quantity']);
        }

        return response()->json([
            'success' => true,
            'data' => [
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
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }),
            ],
            'message' => 'Order created successfully',
        ], 201);
    }

    /**
     * Update an existing order
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'table_number' => 'sometimes|string|max:50',
            'waiter_name' => 'sometimes|string|max:100',
            'type' => 'sometimes|in:dine-in,take-away,delivery',
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

        $order->update($request->only(['table_number', 'waiter_name', 'type']));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
            ],
            'message' => 'Order updated successfully',
        ]);
    }

    /**
     * Delete an order
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        // Restore product quantities
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('quantity', $item->quantity);
            }
        }

        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ]);
    }

    /**
     * Get order items
     */
    public function getItems($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        $items = $order->items()->with('product')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Add item to order
     */
    public function addItem(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
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

        $product = Product::find($request->product_id);
        $totalPrice = $product->price * $request->quantity;

        $item = $order->items()->create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'unit_price' => $product->price,
            'total_price' => $totalPrice,
            'price' => $product->price,
            'product_name' => $product->name,
            'product_rate' => $product->price,
        ]);

        // Update order total
        $order->increment('total_amount', $totalPrice);

        // Decrement product quantity
        $product->decrement('quantity', $request->quantity);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ],
            'message' => 'Item added successfully',
        ], 201);
    }

    /**
     * Update order item
     */
    public function updateItem(Request $request, $id, $itemId)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        $item = OrderItem::find($itemId);

        if (!$item || $item->order_id != $id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Item not found',
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
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

        // Restore old quantity
        $oldQuantity = $item->quantity;
        $product = Product::find($item->product_id);
        $product->increment('quantity', $oldQuantity);

        // Update item
        $newQuantity = $request->quantity;
        $newTotalPrice = $item->unit_price * $newQuantity;
        
        $order->decrement('total_amount', $item->total_price);
        
        $item->update([
            'quantity' => $newQuantity,
            'total_price' => $newTotalPrice,
        ]);

        $order->increment('total_amount', $newTotalPrice);

        // Decrement new quantity
        $product->decrement('quantity', $newQuantity);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price,
            ],
            'message' => 'Item updated successfully',
        ]);
    }

    /**
     * Delete order item
     */
    public function deleteItem($id, $itemId)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order not found',
                ],
            ], 404);
        }

        $item = OrderItem::find($itemId);

        if (!$item || $item->order_id != $id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Item not found',
                ],
            ], 404);
        }

        // Restore product quantity
        $product = Product::find($item->product_id);
        if ($product) {
            $product->increment('quantity', $item->quantity);
        }

        // Update order total
        $order->decrement('total_amount', $item->total_price);

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
        ]);
    }
}